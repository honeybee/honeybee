<?php

namespace Honeybee\Infrastructure\Migration;

interface MigrationServiceInterface
{
    public function migrate($target_name = null, $target_version = null);

    public function getPendingMigrations($target_name = null, $target_version = null);

    public function getExecutedMigrations($target_name = null, $target_version = null);

    public function getMigrationTargetMap();

    public function getMigrationList($target_name = null);

    public function getMigrationTarget($target_name);
}
