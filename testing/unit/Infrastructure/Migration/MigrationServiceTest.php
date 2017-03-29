<?php

namespace Honeybee\Tests\Infrastructure\Migration;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Migration\MigrationList;
use Honeybee\Infrastructure\Migration\MigrationService;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Honeybee\Infrastructure\Migration\MigrationTargetMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Honeybee\Infrastructure\Migration\StructureVersionList;
use Honeybee\Infrastructure\Migration\StructureVersionInterface;
use Honeybee\Infrastructure\Migration\MigrationInterface;

class MigrationServiceTest extends TestCase
{
    public function testGetMigrationTargetMap()
    {
        $migration_service = new MigrationService(
            new ArrayConfig([]),
            $migration_target_map = new MigrationTargetMap
        );

        $this->assertSame($migration_target_map, $migration_service->getMigrationTargetMap());
    }

    public function testGetMigrationTarget()
    {
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertSame($mock_migration_target, $migration_service->getMigrationTarget('mock_migration_target'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetMigrationTargetMissing()
    {
        $migration_service = new MigrationService(new ArrayConfig([]), new MigrationTargetMap);
        $migration_service->getMigrationTarget('mock_migration_target');
    } // @codeCoverageIgnore

    public function testGetMigrationList()
    {
        $migration_list = new MigrationList;
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertSame($migration_list, $migration_service->getMigrationList('mock_migration_target'));
    }

    public function testGetPendingMigrationsNone()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $structure_version_list = new StructureVersionList('executed_versions', [$mock_structure_version]);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals(new MigrationList, $migration_service->getPendingMigrations('mock_migration_target'));
    }

    public function testGetPendingMigrationsNoTarget()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('2');
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $structure_version_list = new StructureVersionList('executed_versions', [$mock_structure_version]);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals($migration_list, $migration_service->getPendingMigrations('mock_migration_target'));
    }

    public function testGetPendingMigrations()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->twice()->withNoArgs()->andReturn('2');
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $structure_version_list = new StructureVersionList('executed_versions', [$mock_structure_version]);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals($migration_list, $migration_service->getPendingMigrations('mock_migration_target', '2'));
    }

    public function testGetExecutedMigrationsNone()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $migration_list = new MigrationList([$mock_migration]);
        $structure_version_list = new StructureVersionList('executed_versions');
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals(new MigrationList, $migration_service->getExecutedMigrations('mock_migration_target'));
    }

    public function testGetExecutedMigrationsNoTarget()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $structure_version_list = new StructureVersionList('executed_versions', [$mock_structure_version]);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals($migration_list, $migration_service->getExecutedMigrations('mock_migration_target'));
    }

    public function testGetExecutedMigrations()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->twice()->withNoArgs()->andReturn('1');
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $structure_version_list = new StructureVersionList('executed_versions', [$mock_structure_version]);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals($migration_list, $migration_service->getExecutedMigrations('mock_migration_target', '1'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testMigrateMissing()
    {
        $migration_service = new MigrationService(new ArrayConfig([]), new MigrationTargetMap);

        $this->assertEquals(new MigrationList, $migration_service->migrate('mock_migration_target'));
    }

    public function testMigrateNone()
    {
        $migration_list = new MigrationList;
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getLatestStructureVersion')->once()->withNoArgs()->andReturnNull();
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals($migration_list, $migration_service->migrate('mock_migration_target'));
    }

    public function testMigrateNew()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->times(3)->withNoArgs()->andReturn('1');
        $migration_list = new MigrationList([$mock_migration]);
        $structure_version_list = new StructureVersionList('executed_versions');
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getLatestStructureVersion')->once()->withNoArgs()->andReturnNull();
        $mock_migration_target->shouldReceive('getMigrationList')->times(3)->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $mock_migration->shouldReceive('migrate')->once()->with(
            Mockery::on(function (MigrationTargetInterface $migration_target) use ($mock_migration_target) {
                $this->assertSame($mock_migration_target, $migration_target);
                return true;
            }),
            'up'
        )->andReturnNull();
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals($migration_list, $migration_service->migrate('mock_migration_target'));
    }

    public function testMigrateUp()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $mock_migration->shouldNotReceive('migrate');
        $mock_migration2 = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration2->shouldReceive('getVersion')->twice()->withNoArgs()->andReturn('2');
        $migration_list = new MigrationList([$mock_migration, $mock_migration2]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->twice()->withNoArgs()->andReturn('1');
        $mock_structure_version2 = Mockery::mock(StructureVersionInterface::CLASS);
        $structure_version_list = new StructureVersionList('executed_versions', [$mock_structure_version]);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getLatestStructureVersion')
            ->once()->withNoArgs()->andReturn($mock_structure_version);
        $mock_migration_target->shouldReceive('getMigrationList')->twice()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $mock_migration2->shouldReceive('migrate')->once()->with(
            Mockery::on(function (MigrationTargetInterface $migration_target) use ($mock_migration_target) {
                $this->assertSame($mock_migration_target, $migration_target);
                return true;
            }),
            'up'
        )->andReturnNull();
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals(
            new MigrationList([$mock_migration2]),
            $migration_service->migrate('mock_migration_target', '2')
        );
    }

    public function testMigrateDown()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->twice()->withNoArgs()->andReturn('1');
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->twice()->withNoArgs()->andReturn('1');
        $structure_version_list = new StructureVersionList('executed_versions', [$mock_structure_version]);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getLatestStructureVersion')
            ->once()->withNoArgs()->andReturn($mock_structure_version);
        $mock_migration_target->shouldReceive('getMigrationList')->twice()->withNoArgs()->andReturn($migration_list);
        $mock_migration_target->shouldReceive('getStructureVersionList')
            ->once()->withNoArgs()->andReturn($structure_version_list);
        $mock_migration->shouldReceive('migrate')->once()->with(
            Mockery::on(function (MigrationTargetInterface $migration_target) use ($mock_migration_target) {
                $this->assertSame($mock_migration_target, $migration_target);
                return true;
            }),
            'down'
        )->andReturnNull();
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals($migration_list, $migration_service->migrate('mock_migration_target', '0'));
    }


    public function testMigrateSame()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getLatestStructureVersion')
            ->once()->withNoArgs()->andReturn($mock_structure_version);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals(new MigrationList, $migration_service->migrate('mock_migration_target', '1'));
    }

    public function testMigrateSameNoTarget()
    {
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $migration_list = new MigrationList([$mock_migration]);
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getLatestStructureVersion')
            ->once()->withNoArgs()->andReturn($mock_structure_version);
        $mock_migration_target->shouldReceive('getMigrationList')->once()->withNoArgs()->andReturn($migration_list);
        $migration_target_map = new MigrationTargetMap(['mock_migration_target' => $mock_migration_target]);
        $migration_service = new MigrationService(new ArrayConfig([]), $migration_target_map);

        $this->assertEquals(new MigrationList, $migration_service->migrate('mock_migration_target'));
    }
}
