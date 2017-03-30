<?php

namespace Honeybee\Tests\Infrastructure\Migration;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Migration\MigrationInterface;
use Honeybee\Infrastructure\Migration\MigrationList;
use Honeybee\Infrastructure\Migration\MigrationLoaderInterface;
use Honeybee\Infrastructure\Migration\MigrationTarget;
use Honeybee\Infrastructure\Migration\StructureVersion;
use Honeybee\Infrastructure\Migration\StructureVersionInterface;
use Honeybee\Infrastructure\Migration\StructureVersionList;
use Honeybee\Tests\TestCase;
use Mockery;

class MigrationTargetTest extends TestCase
{
    public function testGetName()
    {
        $migration_target = $this->makeMigrationTarget();
        $this->assertEquals('mock_target', $migration_target->getName());
    }

    public function testGetConfig()
    {
        $config = ['test' => 'data'];
        $migration_target = $this->makeMigrationTarget(null, true, $config);
        $this->assertEquals(new ArrayConfig($config), $migration_target->getConfig());
    }

    public function testIsActivated()
    {
        $migration_target = $this->makeMigrationTarget();
        $this->assertTrue($migration_target->isActivated());
    }

    public function testIsActivatedNotActivated()
    {
        $migration_target = $this->makeMigrationTarget(null, false);
        $this->assertFalse($migration_target->isActivated());
    }

    public function testGetTargetConnector()
    {
        $mock_connector = new \stdClass;
        $mock_connecter_service = Mockery::mock(ConnectorServiceInterface::CLASS);
        $mock_connecter_service->shouldReceive('getConnector')
            ->once()->with('mock_connection')->andReturn($mock_connector);
        $config = ['target_connection' => 'mock_connection'];
        $migration_target = $this->makeMigrationTarget(null, true, $config, null, null, $mock_connecter_service);

        $this->assertSame($mock_connector, $migration_target->getTargetConnector());
    }

    public function testGetStructureVersionListNew()
    {
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturnNull();
        $config = ['version_list_reader' => 'mock_reader'];
        $migration_target = $this->makeMigrationTarget(null, true, $config, null, $mock_data_access_service);

        $this->assertEquals(
            new StructureVersionList('mock_target::version_list'),
            $migration_target->getStructureVersionList()
        );
    }

    public function testGetStructureVersionListExisting()
    {
        $structure_version_list = new StructureVersionList('mock_target::version_list');
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturn($structure_version_list);
        $config = ['version_list_reader' => 'mock_reader'];
        $migration_target = $this->makeMigrationTarget(null, true, $config, null, $mock_data_access_service);

        $this->assertSame($structure_version_list, $migration_target->getStructureVersionList());
    }

    public function testGetLatestStructureVersionNone()
    {
        $structure_version_list = new StructureVersionList('mock_target::version_list');
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturn($structure_version_list);
        $config = ['version_list_reader' => 'mock_reader'];
        $migration_target = $this->makeMigrationTarget(null, true, $config, null, $mock_data_access_service);

        $this->assertNull($migration_target->getLatestStructureVersion());
    }

    public function testGetLatestStructureVersion()
    {
        $structure_version = new StructureVersion;
        $structure_version2 = new StructureVersion;
        $structure_version_list = new StructureVersionList(
            'mock_target::version_list',
            [$structure_version, $structure_version2]
        );
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturn($structure_version_list);
        $config = ['version_list_reader' => 'mock_reader'];
        $migration_target = $this->makeMigrationTarget(null, true, $config, null, $mock_data_access_service);

        $this->assertSame($structure_version2, $migration_target->getLatestStructureVersion());
    }

    public function testGetMigrationList()
    {
        $migration_list = new MigrationList;
        $mock_migration_loader = Mockery::mock(MigrationLoaderInterface::CLASS);
        $mock_migration_loader->shouldReceive('loadMigrations')->once()->withNoArgs()->andReturn($migration_list);
        $migration_target = $this->makeMigrationTarget(null, true, [], $mock_migration_loader);

        $this->assertSame($migration_list, $migration_target->getMigrationList());
    }

    public function testBumpStructureVersionListUp()
    {
        $structure_version_list = new StructureVersionList('mock_target::version_list');
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $mock_migration_loader = Mockery::mock(MigrationLoaderInterface::CLASS);
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturn($structure_version_list);
        $mock_data_access_service->shouldReceive('writeTo')->once()->with(
            'mock_writer',
            Mockery::on(function (StructureVersionList $version_list) use ($structure_version_list) {
                $this->assertSame($structure_version_list, $version_list);
                return true;
            })
        )->andReturnNull();
        $config = ['version_list_reader' => 'mock_reader', 'version_list_writer' => 'mock_writer'];
        $migration_target = $this->makeMigrationTarget(
            null,
            true,
            $config,
            $mock_migration_loader,
            $mock_data_access_service
        );

        $this->assertNull($migration_target->bumpStructureVersion($mock_migration, 'up'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testBumpStructureVersionListDownNone()
    {
        $structure_version_list = new StructureVersionList('mock_target::version_list');
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturn($structure_version_list);
        $config = ['version_list_reader' => 'mock_reader'];
        $migration_target = $this->makeMigrationTarget(null, true, $config, null, $mock_data_access_service);

        $migration_target->bumpStructureVersion($mock_migration, 'down');
    } //@codeCoverageIgnore

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testBumpStructureVersionListDownInvalid()
    {
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $structure_version_list = new StructureVersionList('mock_target::version_list', [$mock_structure_version]);
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $mock_migration_loader = Mockery::mock(MigrationLoaderInterface::CLASS);
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturn($structure_version_list);
        $config = ['version_list_reader' => 'mock_reader'];
        $migration_target = $this->makeMigrationTarget(
            null,
            true,
            $config,
            $mock_migration_loader,
            $mock_data_access_service
        );

        $migration_target->bumpStructureVersion($mock_migration, 'down');
    } //@codeCoverageIgnore

    public function testBumpStructureVersionListDown()
    {
        $this->markTestIncomplete('Uncertain about expectations. Clarification required.');
        $mock_structure_version = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version2 = Mockery::mock(StructureVersionInterface::CLASS);
        $mock_structure_version2->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('2');
        $structure_version_list = new StructureVersionList(
            'mock_target::version_list',
            [$mock_structure_version, $mock_structure_version2]
        );
        $mock_migration = Mockery::mock(MigrationInterface::CLASS);
        $mock_migration->shouldReceive('getVersion')->once()->withNoArgs()->andReturn('1');
        $mock_migration_loader = Mockery::mock(MigrationLoaderInterface::CLASS);
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('readFrom')
            ->once()->with('mock_reader', 'mock_target::version_list')->andReturn($structure_version_list);
        $config = ['version_list_reader' => 'mock_reader'];
        $migration_target = $this->makeMigrationTarget(
            null,
            true,
            $config,
            $mock_migration_loader,
            $mock_data_access_service
        );

        $migration_target->bumpStructureVersion($mock_migration, 'down');
    }

    private function makeMigrationTarget(
        $name = null,
        $activated = true,
        $config = [],
        $mock_filesystem_loader = null,
        $mock_data_access_service = null,
        $mock_connecter_service = null
    ) {
        return new MigrationTarget(
            $name ?: 'mock_target',
            $activated === true,
            new ArrayConfig($config),
            $mock_filesystem_loader ?: Mockery::mock(MigrationLoaderInterface::CLASS),
            $mock_data_access_service ?: Mockery::mock(DataAccessServiceInterface::CLASS),
            $mock_connecter_service ?: Mockery::mock(ConnectorServiceInterface::CLASS)
        );
    }
}
