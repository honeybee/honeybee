<?php

namespace Honeybee\Infrastructure\Migration;

interface MigrationServiceInterface
{
    public function migrate($target_name, $target_version = null);

    public function getPendingMigrations($target_name, $target_version = null);

    public function getExecutedMigrations($target_name, $target_version = null);

    public function getMigrationTargetMap();

    public function getMigrationList($target_name);

    public function getMigrationTarget($target_name);
}
