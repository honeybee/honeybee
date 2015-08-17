<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Pdo;

use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\IStorageKey;
use PDO;

class StorageWriter extends GenericAccess implements StorageWriterInterface
{
    public function write(IStorageKey $key, $data, SettingsInterface $settings = null)
    {
        if ($this->mapTableName($key, $settings) === $this->config->get('table')) {
            $this->writeData($key, $data, $settings);
        } else {
            $this->writeStructureVersion($key, $data, $settings);
        }
    }

    public function delete(IStorageKey $key, SettingsInterface $settings = null)
    {
        $table = $this->mapTableName($key, $settings);
        $identifier = $key->getIdentifier();

        $delete_statement = $pdo_handle->prepare(sprintf('DELETE FROM %s WHERE identifier=:identifier', $table));
        $delete_statement->bindValue(':identifier', $identifier);
        $this->executeStatement($delete_statement);
    }

    protected function writeData(IStorageKey $key, $data, SettingsInterface $settings = null)
    {
        $table = $this->mapTableName($key, $settings);
        $identifier = $key->getIdentifier();

        $pdo_handle = $this->connector->getConnection();
        $events_json = json_encode($data['events']);

        $update_statement = $pdo_handle->prepare(
            sprintf('UPDATE %s SET events=:events WHERE identifier=:identifier', $table)
        );
        $update_statement->bindValue(':events', $events_json);
        $update_statement->bindValue(':identifier', $identifier);
        $this->executeStatement($update_statement);

        if ($update_statement->rowCount() !== 1) {
            $insert_statement = $pdo_handle->prepare(
                sprintf('INSERT INTO %s (identifier, events) VALUES (:identifier, :events)', $table)
            );
            $insert_statement->bindValue(':events', $events_json);
            $insert_statement->bindValue(':identifier', $identifier);
            $this->executeStatement($insert_statement);
        }
    }

    protected function writeStructureVersion(IStorageKey $key, $data, SettingsInterface $settings = null)
    {
        $table = $this->mapTableName($key, $settings);
        $identifier = $key->getIdentifier();

        $pdo_handle = $this->connector->getConnection();
        $version_history = json_encode($data['version_history']);

        $update_statement = $pdo_handle->prepare(
            sprintf('UPDATE %s SET version_history=:version_history WHERE identifier=:identifier', $table)
        );
        $update_statement->bindValue(':version_history', $version_history);
        $update_statement->bindValue(':identifier', $identifier);
        $this->executeStatement($update_statement);

        if ($update_statement->rowCount() !== 1) {
            $insert_statement = $pdo_handle->prepare(
                sprintf('INSERT INTO %s (identifier, version_history) VALUES (:identifier, :version_history)', $table)
            );
            $insert_statement->bindValue(':version_history', $version_history);
            $insert_statement->bindValue(':identifier', $identifier);
            $this->executeStatement($insert_statement);
        }
    }
}
