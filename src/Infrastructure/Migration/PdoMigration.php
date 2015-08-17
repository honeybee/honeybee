<?php

namespace Honeybee\Infrastructure\Migration;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\DataAccess\Storage\Pdo\GenericAccess as PdoStorageAccess;

abstract class PdoMigration extends Migration
{
    abstract protected function getTableName(MigrationTargetInterface $migration_target);

    protected function createDataTable(MigrationTargetInterface $migration_target)
    {
        $pdo_handle = $this->getConnection($migration_target);
        $table_name = $this->getTableName($migration_target);

        $create_table_sql = sprintf(
            'CREATE TABLE %s ( identifier varchar PRIMARY KEY, events json );',
            $table_name
        );
        $statement = $pdo_handle->prepare($create_table_sql);

        if (!$statement->execute()) {
            throw new RuntimeError(
                sprintf(
                    'Failed to create table %s. Reason: %s',
                    $table_name,
                    print_r($statement->errorinfo(), true)
                )
            );
        }
    }

    protected function createStructureVersionTable(MigrationTargetInterface $migration_target)
    {
        $pdo_handle = $this->getConnection($migration_target);
        $table_name = PdoStorageAccess::STRUCTURE_VERSION_TABLE;

        $create_table_sql = sprintf(
            'CREATE TABLE %s ( identifier varchar PRIMARY KEY, version_history json );',
            $table_name
        );
        $statement = $pdo_handle->prepare($create_table_sql);

        if (!$statement->execute()) {
            throw new RuntimeError(
                sprintf(
                    'Failed to create table %s. Reason: %s',
                    $table_name,
                    print_r($statement->errorinfo(), true)
                )
            );
        }
    }

    protected function dropTable(MigrationTarget $migration_target, $table_name)
    {
        $pdo_handle = $this->getConnection($migration_target);
        $drop_table_sql = sprintf('DROP TABLE %s;', $table_name);

        if (!$pdo_handle->execute($drop_table_sql)) {
            throw new RuntimeError(sprintf('Failed to drop table %s.', $table_name));
        }
    }
}
