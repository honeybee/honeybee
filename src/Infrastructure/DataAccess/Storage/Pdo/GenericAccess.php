<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Pdo;

use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\DataAccess\Storage\Storage;
use Honeybee\Infrastructure\DataAccess\Storage\IStorageKey;
use PDOStatement;

class GenericAccess extends Storage
{
    const STRUCTURE_VERSION_TABLE = 'structure_version_list';

    protected function mapTableName(IStorageKey $key, SettingsInterface $settings = null)
    {
        return ($key->getType() === 'structure-version')
            ? self::STRUCTURE_VERSION_TABLE
            : $this->config->get('table');
    }

    protected function executeStatement(PDOStatement $statement)
    {
        if (!$statement->execute()) {
            throw new RuntimeError(
                sprintf(
                    "Failed to execute statement. Reason: %s",
                    print_r($insert_statement->errorinfo(), true)
                )
            );
        }
    }
}
