<?php

namespace DigitalMarketingFramework\Collector\Pardot\DataCollector;

use DigitalMarketingFramework\Collector\Core\DataCollector\DataCollector;
use DigitalMarketingFramework\Collector\Core\Model\Configuration\CollectorConfigurationInterface;
use DigitalMarketingFramework\Collector\Core\Model\Result\DataCollectorResult;
use DigitalMarketingFramework\Collector\Core\Model\Result\DataCollectorResultInterface;
use DigitalMarketingFramework\Collector\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Collector\Pardot\Connector\PardotConnector;
use DigitalMarketingFramework\Collector\Pardot\Connector\PardotConnectorInterface;
use DigitalMarketingFramework\Collector\Pardot\Exception\PardotConnectorException;
use DigitalMarketingFramework\Collector\Pardot\Model\Identifier\PardotProspectIdentifier;
use DigitalMarketingFramework\Collector\Pardot\Model\Identifier\PardotVisitorIdentifier;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Exception\InvalidIdentifierException;
use DigitalMarketingFramework\Core\Model\Identifier\IdentifierInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;

class PardotDataCollector extends DataCollector
{
    public const KEY_OUTPUT_MODE = 'outputMode';

    public const DEFAULT_OUTPUT_MODE = 'simple';

    public const STATUS_CODE_INVALID_VISITOR_ID = 24;

    /** @var array<string,mixed> */
    protected array $credentials;

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        CollectorConfigurationInterface $collectorConfiguration,
        protected PardotConnectorInterface $pardotConnector = new PardotConnector(),
    ) {
        parent::__construct($keyword, $registry, $collectorConfiguration);

        $connectorConfig = $this->registry->getGlobalConfiguration()->get('digitalmarketingframework_collector_pardot');
        $this->pardotConnector->environment($connectorConfig['api']['environment'] ?? PardotConnectorInterface::ENVIRONMENT_PRODUCTION);
        $this->pardotConnector->version($connectorConfig['api']['version'] ?? 3);

        $this->credentials = $connectorConfig['api']['credentials'] ?? [];
    }

    /**
     * @return array<mixed>|false
     */
    protected function fetchProspect(string $prospectId): array|false
    {
        $outputMode = $this->getConfig(static::KEY_OUTPUT_MODE);

        return $this->pardotConnector->prospect()->outputMode($outputMode)->read(['id' => $prospectId]);
    }

    /**
     * @param array<mixed> $visitor
     */
    protected function getProspectIdFromVisitorData(array $visitor): ?string
    {
        $prospectId = null;

        if (is_array($visitor) && isset($visitor['prospect_id'])) {
            $prospectId = (string)$visitor['prospect_id'];
        }

        return $prospectId ?: null;
    }

    /**
     * @return array<mixed>|false
     */
    protected function fetchVisitor(string $visitorId): array|false
    {
        try {
            return $this->pardotConnector->visitor()->outputMode('mobile')->read(['id' => $visitorId]);
        } catch (PardotConnectorException $e) {
            $this->logger->error('Pardot API - ' . $e->getMessage());
            if ((int)$e->getCode() === static::STATUS_CODE_INVALID_VISITOR_ID) {
                // this may have been an attempt to guess a visitor id, so we throw an exception specific for that
                throw new InvalidIdentifierException(sprintf('Pardot visitor id "%s" was not valid', $visitorId));
            }

            return false;
        }
    }

    protected function login(): void
    {
        if ($this->credentials === []) {
            throw new PardotConnectorException('credentials are empty');
        }

        $this->pardotConnector->authenticate($this->credentials);

        if (!$this->pardotConnector->isAuthenticated()) {
            throw new PardotConnectorException('authentication not successful');
        }
    }

    /**
     * @throws InvalidIdentifierException
     */
    protected function collect(IdentifierInterface $identifier): ?DataCollectorResultInterface
    {
        $identifiers = [$identifier];

        try {
            if (!$this->pardotConnector->isAuthenticated()) {
                $this->login();
            }

            if ($identifier instanceof PardotVisitorIdentifier) {
                // starting with a visitor id
                $visitorId = $identifier->getVisitorId();
                $visitor = $this->fetchVisitor($visitorId);

                $prospectId = $this->getProspectIdFromVisitorData($visitor);
                if ($prospectId === null) {
                    return null;
                }

                array_unshift($identifiers, new PardotProspectIdentifier($prospectId));
            } elseif ($identifier instanceof PardotProspectIdentifier) {
                // starting with a prospect id
                $prospectId = $identifier->getProspectId();
            } else {
                // unknown identifier
                throw new DigitalMarketingFrameworkException('Pardot identifier seems to be invalid');
            }

            // fetch prospect data
            $prospect = $this->fetchProspect($prospectId);
            if (!is_array($prospect)) {
                throw new DigitalMarketingFrameworkException('Failed to load prospect');
            }

            // cast prospect data to official data format and return
            $data = GeneralUtility::castArrayToData($prospect);

            return new DataCollectorResult($data, $identifiers);
        } catch (PardotConnectorException $e) {
            $this->logger->error('Pardot API - ' . $e->getMessage());

            return null;
        }
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();

        $outputMode = new StringSchema(static::DEFAULT_OUTPUT_MODE);
        $outputMode->getAllowedValues()->addValue('simple');
        $outputMode->getAllowedValues()->addValue('full');
        $outputMode->getRenderingDefinition()->setFormat(RenderingDefinitionInterface::FORMAT_SELECT);

        $schema->addProperty(static::KEY_OUTPUT_MODE, $outputMode);

        return $schema;
    }
}
