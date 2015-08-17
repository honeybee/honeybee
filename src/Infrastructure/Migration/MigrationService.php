<?php

namespace Honeybee\Infrastructure\Migration;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Common\Error\RuntimeError;

class MigrationService implements MigrationServiceInterface
{
    const FILTER_PENDING = 'pending';

    const FILTER_EXECUTED = 'executed';

    protected $config;

    protected $migration_target_map;

    public function __construct(ConfigInterface $config, MigrationTargetMap $migration_target_map)
    {
        $this->config = $config;
        $this->migration_target_map = $migration_target_map;
    }

    public function migrate($target_name = null, $target_version = null)
    {
        $executed_migrations = new MigrationList();
        $direction = $this->getDirection($target_name, $target_version);
        if (!$direction) {
            return $executed_migrations;
        }

        $target_version = is_null($target_version)
            ? $this->getDefaultTargetVersion($target_name, $direction)
            : $target_version;

        if ($direction === MigrationInterface::MIGRATE_UP) {
            $migrations = $this->getPendingMigrations($target_name, $target_version);
        } else {
            $migrations = $this->getExecutedMigrations($target_name, $target_version);
        }

        foreach ($migrations as $migration) {
            $migration->migrate($this->getMigrationTarget($target_name), $direction);
            $executed_migrations->push($migration);
        }

        return $executed_migrations;
    }

    public function getMigrationTarget($target_name)
    {
        if (!$this->migration_target_map->hasKey($target_name)) {
            throw new RuntimeError(sprintf("Unable to find migration target %s.", $target_name));
        }

        return $this->migration_target_map->getItem($target_name);
    }

    public function getPendingMigrations($target_name = null, $target_version = null)
    {
        return $this->getFilteredMigrations($target_name, self::FILTER_PENDING)->filter(
            function (MigrationInterface $migration) use ($target_version) {
                if (is_null($target_version)) {
                    return true;
                }
                return (int)$target_version >= (int)$migration->getVersion();
            }
        );
    }

    public function getExecutedMigrations($target_name = null, $target_version = null)
    {
        return $this->getFilteredMigrations($target_name, self::FILTER_EXECUTED)->filter(
            function(MigrationInterface $migration) use ($target_version) {
                if (is_null($target_version)) {
                    return true;
                }
                return (int)$target_version <= (int)$migration->getVersion();
            }
        );
    }

    public function getMigrationList($target_name = null)
    {
        return $this->getMigrationTarget($target_name)->getMigrationList();
    }

    public function getMigrationTargetMap()
    {
        return $this->migration_target_map;
    }

    protected function getDefaultTargetVersion($target_name, $direction)
    {
        if ($direction === MigrationInterface::MIGRATE_UP) {
            $latest_migration = $this->getMigrationList($target_name)->getLast();
            $target_version = $latest_migration->getVersion();
        } else {
            $first_migration = $this->getMigrationList($target_name)->getFirst();
            $target_version = $first_migration->getVersion();
        }

        return $target_version;
    }

    protected function getDirection($target_name = null, $target_version = null)
    {
        $migration_target = $this->getMigrationTarget($target_name);
        $latest_structure_version = $migration_target->getLatestStructureVersion();

        $migration_list = $migration_target->getMigrationList();
        if ($migration_list->isEmpty()) {
            return null;
        }

        if (!$latest_structure_version) {
            return MigrationInterface::MIGRATE_UP;
        }

        if (is_null($target_version)) {
            $target_version = $migration_list->getLast()->getVersion();
        }

        $target_version = (int)$target_version;
        $latest_version = (int)$latest_structure_version->getVersion();

        if ($latest_version < $target_version) {
            return MigrationInterface::MIGRATE_UP;
        } else if ($latest_version > $target_version) {
            return MigrationInterface::MIGRATE_DOWN;
        } else {
            return null;
        }
    }

    protected function getFilteredMigrations($target_name, $filter_type = self::FILTER_EXECUTED)
    {
        $migration_target = $this->getMigrationTarget($target_name);
        $structure_version_list = $migration_target->getStructureVersionList();
        $executed_versions = [];
        foreach ($structure_version_list as $structure_version) {
            $executed_versions[] = $structure_version->getVersion();
        }

        return $migration_target->getMigrationList()->filter(
            function (MigrationInterface $migration) use ($executed_versions, $filter_type) {
                if ($filter_type === self::FILTER_EXECUTED) {
                    return in_array($migration->getVersion(), $executed_versions);
                } else {
                    return !in_array($migration->getVersion(), $executed_versions)
                        || empty($executed_versions);
                }
            }
        );
    }
}
