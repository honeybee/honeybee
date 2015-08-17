<?php

namespace Honeybee\Infrastructure\Migration;

interface MigrationLoaderInterface
{
    /**
     * @return MigrationList
     */
    public function loadMigrations();
}
