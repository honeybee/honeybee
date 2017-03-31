<?php

namespace Honeybee\Infrastructure\Migration;

use Honeybee\Common\Error\RuntimeError;
use Trellis\Common\Object;

abstract class Migration extends Object implements MigrationInterface
{
    protected $name;

    protected $version;

    abstract protected function up(MigrationTargetInterface $migration_target);

    abstract protected function down(MigrationTargetInterface $migration_target);

    final public function migrate(MigrationTargetInterface $migration_target, $direction = self::MIGRATE_UP)
    {
        $this->guardMigrationTarget($migration_target);
        $this->guardDirection($direction);

        if ($direction === self::MIGRATE_DOWN) {
            $this->guardReversal();
            $this->down($migration_target);
        } else {
            $this->up($migration_target);
        }

        $migration_target->bumpStructureVersion($this, $direction);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    protected function getTimestamp()
    {
        if (!preg_match('/\d{14}/', static::class, $matches)) {
            throw new RuntimeError(sprintf("Unable to parse migration timestamp from: %s", static::class));
        }
        return $matches[0];
    }

    protected function getConnection(MigrationTargetInterface $migration_target)
    {
        return $migration_target->getTargetConnector()->getConnection();
    }

    protected function guardMigrationTarget(MigrationTargetInterface $migration_target)
    {
        if (!$migration_target->isActivated()) {
            throw new RuntimeError(
                sprintf(
                    'Not allowed to execute migration %s:%s, migration target %s is deactivated.',
                    $this->getVersion(),
                    $this->getName(),
                    $migration_target->getName()
                )
            );
        }
    }

    protected function guardDirection($direction)
    {
        if (!in_array($direction, [ self::MIGRATE_UP, self::MIGRATE_DOWN ])) {
            throw new RuntimeError(
                sprintf(
                    'Invalid migration direction given: %s to migration %s:%s. Only %s and %s are supported',
                    $direction,
                    $this->getVersion(),
                    $this->getName(),
                    self::MIGRATE_UP,
                    self::MIGRATE_DOWN
                )
            );
        }
    }

    protected function guardReversal()
    {
        if (!$this->isReversible()) {
            throw new RuntimeError(
                sprintf(
                    'Migration %s:%s is marked as non-reversable and does not support downward migration.',
                    $this->getVersion(),
                    $this->getName()
                )
            );
        }
    }
}
