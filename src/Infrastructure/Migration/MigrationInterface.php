<?php

namespace Honeybee\Infrastructure\Migration;

interface MigrationInterface
{
    const MIGRATE_UP = 'up';

    const MIGRATE_DOWN = 'down';

    public function getName();

    public function getDescription($direction = self::MIGRATE_UP);

    public function getVersion();

    public function isReversible();

    public function migrate(MigrationTargetInterface $migration_target, $direction = self::MIGRATE_UP);
}
