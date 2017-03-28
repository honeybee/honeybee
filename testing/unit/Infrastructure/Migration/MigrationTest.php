<?php

namespace Honeybee\Tests\Infrastructure\Migration;

use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\Migration\Migration;
use Honeybee\Infrastructure\Migration\MigrationInterface;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Honeybee\Tests\TestCase;
use Mockery;

class MigrationTest extends TestCase
{
    public function testGetNameNull()
    {
        $mock_migration = Mockery::mock(Migration::CLASS)->makePartial();
        $this->assertNull($mock_migration->getName());
    }

    public function testGetName()
    {
        $mock_migration = Mockery::mock(Migration::CLASS, [['name' => 'mock_migration']])->makePartial();
        $this->assertEquals('mock_migration', $mock_migration->getName());
    }

    public function testGetVersionNull()
    {
        $mock_migration = Mockery::mock(Migration::CLASS)->makePartial();
        $this->assertNull($mock_migration->getVersion());
    }

    public function testGetVersion()
    {
        $mock_migration = Mockery::mock(Migration::CLASS, [['version' => '2020']])->makePartial();
        $this->assertEquals('2020', $mock_migration->getVersion());
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetTimestampInvalid()
    {
        $mock_migration = Mockery::namedMock('InvalidMock', Migration::CLASS)->makePartial();
        $mock_migration->getTimestamp();
    }

    public function testGetTimestamp()
    {
        $mock_migration = Mockery::namedMock('Migration_12345678901234', Migration::CLASS)->makePartial();
        $this->assertEquals('12345678901234', $mock_migration->getTimestamp());
    }

    public function testGetConnection()
    {
        $mock_migration = Mockery::mock(Migration::CLASS)->makePartial();
        $mock_connection = new \stdClass;
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($mock_connection);
        $mock_migration_target = Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('getTargetConnector')->once()->withNoArgs()->andReturn($mock_connector);
        $this->assertSame($mock_connection, $mock_migration->getConnection($mock_migration_target));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testMigrateGuardInactive()
    {
        $mock_migration = Mockery::mock(
            Migration::CLASS,
            [['name' => 'mock_migration', 'version' => '20201234567890']]
        )->makePartial();
        $mock_migration_target= Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('isActivated')->once()->withNoArgs()->andReturnFalse();
        $mock_migration_target->shouldReceive('getName')->once()->withNoArgs()->andReturn('mock_target');
        $mock_migration->migrate($mock_migration_target);
    } //@codeCoverageIgnore

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testMigrateGuardDirection()
    {
        $mock_migration = Mockery::mock(
            Migration::CLASS,
            [['name' => 'mock_migration', 'version' => '20201234567890']]
        )->makePartial();
        $mock_migration_target= Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('isActivated')->once()->withNoArgs()->andReturnTrue();
        $mock_migration->migrate($mock_migration_target, 'invalid');
    } //@codeCoverageIgnore

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testMigrateGuardReversal()
    {
        $mock_migration = Mockery::mock(
            Migration::CLASS,
            [['name' => 'mock_migration', 'version' => '20201234567890']]
        )->makePartial();
        $mock_migration->shouldReceive('isReversible')->once()->withNoArgs()->andReturnFalse();
        $mock_migration_target= Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('isActivated')->once()->withNoArgs()->andReturnTrue();
        $mock_migration->migrate($mock_migration_target, 'down');
    } //@codeCoverageIgnore

    public function testMigrateUp()
    {
        $mock_migration = Mockery::mock(
            Migration::CLASS,
            [['name' => 'mock_migration', 'version' => '20201234567890']]
        )->makePartial();
        $mock_migration_target= Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('isActivated')->once()->withNoArgs()->andReturnTrue();
        $mock_migration_target->shouldReceive('bumpStructureVersion')->once()->with(
            Mockery::on(function (MigrationInterface $migration) use ($mock_migration) {
                $this->assertSame($mock_migration, $migration);
                return true;
            }),
            'up'
        )->andReturnNull();
        $mock_migration->migrate($mock_migration_target, 'up');
    }

    public function testMigrateDown()
    {
        $mock_migration = Mockery::mock(
            Migration::CLASS,
            [['name' => 'mock_migration', 'version' => '20201234567890']]
        )->makePartial();
        $mock_migration->shouldReceive('isReversible')->once()->withNoArgs()->andReturnTrue();
        $mock_migration_target= Mockery::mock(MigrationTargetInterface::CLASS);
        $mock_migration_target->shouldReceive('isActivated')->once()->withNoArgs()->andReturnTrue();
        $mock_migration_target->shouldReceive('bumpStructureVersion')->once()->with(
            Mockery::on(function (MigrationInterface $migration) use ($mock_migration) {
                $this->assertSame($mock_migration, $migration);
                return true;
            }),
            'down'
        )->andReturnNull();
        $mock_migration->migrate($mock_migration_target, 'down');
    }
}
