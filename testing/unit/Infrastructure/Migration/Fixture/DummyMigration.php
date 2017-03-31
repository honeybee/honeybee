<?php

namespace Honeybee\Tests\Infrastructure\Migration\Fixture;

use Honeybee\Infrastructure\Migration\Migration;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;

// @codingStandardsIgnoreLine
class Migration_20170101125959_DummyMigration extends Migration
{
    protected function up(MigrationTargetInterface $migration_target)
    {
    }

    protected function down(MigrationTargetInterface $migration_target)
    {
    }

    public function getDescription($direction = self::MIGRATE_UP)
    {
        if ($direction === MigrationInterface::MIGRATE_UP) {
            return 'Upward migration fixture';
        }
        return 'Downward migration fixture';
    }

    public function isReversible()
    {
        return true;
    }
}
