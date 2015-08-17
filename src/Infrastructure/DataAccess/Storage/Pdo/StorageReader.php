<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Pdo;

use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\DataAccess\Storage\IStorageKey;
use PDO;

class StorageReader extends GenericAccess implements StorageReaderInterface
{
    const READ_ALL_LIMIT = 10;

    protected $next_start_key = null;

    public function readAll(SettingsInterface $settings)
    {
        $data = array();

        $default_limit = $this->config->get('limit', self::READ_ALL_LIMIT);

        return $data;
    }

    public function read(IStorageKey $key, SettingsInterface $settings = null)
    {
        $table = $this->mapTableName($key, $settings);
        $pdo_handle = $this->connector->getConnection();

        $select_statement = $pdo_handle->prepare(
            sprintf('SELECT * FROM %s WHERE identifier=:identifier', $table)
        );
        $select_statement->bindValue(':identifier', $key->getIdentifier());

        if (!$select_statement->execute()) {
            if ($select_statement->errorcode() === '42P01') {
                // table does not exist.
                return null;
            } else {
                throw new RuntimeError(
                    sprintf(
                        'Failed to read data from table for identifier: %s. Reason :%s',
                        $identifier,
                        print_r($select_statement->errorinfo(), true)
                    )
                );
            }
        }

        if ($select_statement->rowcount() === 0) {
            return null;
        }

        $data = $select_statement->fetch(PDO::FETCH_ASSOC);
        if ($table === $this->config->get('table')) {
            $data['events'] = json_decode($data['events'], true);
        } else {
            $data['version_history'] = json_decode($data['version_history'], true);
        }

        return $data;
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }
}
