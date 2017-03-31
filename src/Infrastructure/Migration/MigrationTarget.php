<?php

namespace Honeybee\Infrastructure\Migration;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;

class MigrationTarget implements MigrationTargetInterface
{
    const STORAGE_ID_SUFFIX = 'version_list';

    protected $name;

    protected $is_activated;

    protected $structure_version_list;

    protected $migration_list;

    protected $config;

    protected $migration_loader;

    protected $data_access_service;

    protected $connector_service;

    public function __construct(
        $name,
        $is_activated,
        ConfigInterface $config,
        MigrationLoaderInterface $migration_loader,
        DataAccessServiceInterface $data_access_service,
        ConnectorServiceInterface $connector_service
    ) {
        $this->name = $name;
        $this->is_activated = $is_activated;
        $this->config = $config;
        $this->migration_loader = $migration_loader;
        $this->data_access_service = $data_access_service;
        $this->connector_service = $connector_service;
    }

    public function getStructureVersionList()
    {
        if (!$this->structure_version_list) {
            $this->structure_version_list = $this->loadStructureVersionList();
        }

        return $this->structure_version_list;
    }

    public function bumpStructureVersion(MigrationInterface $migration, $direction)
    {
        if ($direction === MigrationInterface::MIGRATE_UP) {
            $this->pushVersion($migration);
        } else {
            $this->popVersion($migration);
        }

        if ($this->getStructureVersionList()->getSize() > 0) {
            $this->data_access_service->writeTo(
                $this->config->get('version_list_writer'),
                $this->structure_version_list
            );
        } else {
            $this->data_access_service->deleteFrom(
                $this->config->get('version_list_writer'),
                $this->structure_version_list->getIdentifier()
            );
        }
    }

    public function getMigrationList()
    {
        if (!$this->migration_list) {
            $this->migration_list = $this->migration_loader->loadMigrations();
        }

        return $this->migration_list;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isActivated()
    {
        return $this->is_activated;
    }

    public function getTargetConnector()
    {
        return $this->connector_service->getConnector(
            $this->config->get('target_connection')
        );
    }

    public function getLatestStructureVersion()
    {
        return $this->getStructureVersionList()->getLast();
    }

    protected function pushVersion(MigrationInterface $migration)
    {
        $new_version = new StructureVersion(
            [
                'target_name' => $this->getName(),
                'version' => $migration->getVersion()
            ]
        );

        $this->getStructureVersionList()->push($new_version);

        return $new_version;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function popVersion(MigrationInterface $migration)
    {
        $latest_structure_version = $this->getStructureVersionList()->pop();

        if (!$latest_structure_version) {
            throw new RuntimeError('No existing migration structure versions found.');
        }

        $latest_version = $latest_structure_version->getVersion();
        $migration_version = $migration->getVersion();

        if (!($latest_version !== $migration_version)) {
            throw new RuntimeError(
                sprintf(
                    'Version mismatch while trying to bump the structure version. '
                    . 'Expected migration version to be: %s, %s given instead.',
                    $latest_version,
                    $migration_version
                )
            );
        }

        return $latest_structure_version;
    }

    protected function loadStructureVersionList()
    {
        $identifier = sprintf('%s::%s', $this->getName(), self::STORAGE_ID_SUFFIX);

        $structure_version_list = $this->data_access_service->readFrom(
            $this->config->get('version_list_reader'),
            $identifier
        );

        return $structure_version_list ?: new StructureVersionList($identifier);
    }
}
