<?php

namespace Honeybee\Tests\Infrastructure\Fixture;

use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Honeybee\Infrastructure\Fixture\FixtureTargetInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Tests\Infrastructure\Fixture\Fixture\MockFixture;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Finder\Finder;

class FixtureTest extends TestCase
{
    protected $aggregate_root_type_map;

    public function setUp()
    {
        $author_aggregate_root_type = new AuthorType();
        $this->aggregate_root_type_map = new AggregateRootTypeMap(
            [
                $author_aggregate_root_type->getPrefix() => $author_aggregate_root_type
            ]
        );
    }

    /**
     * @dataProvider provideFixtureData
     */
    public function testExecute($fixture_file, $expectation_file)
    {
        $mock_command_bus = Mockery::mock(CommandBusInterface::CLASS);
        $mock_command_bus->shouldReceive('post')->once()->with(Mockery::on(
            function (CommandInterface $command) use ($expectation_file) {
                $fixture_data = $command->toArray();
                $expectation = include $expectation_file;
                $this->assertEquals($expectation, $command->toArray());
                return true;
            }
        ));

        $mock_filesystem_service = Mockery::mock(FilesystemServiceInterface::CLASS);
        $mock_finder = Mockery::mock(Finder::CLASS);

        $fixture = new MockFixture(
            $this->aggregate_root_type_map,
            $mock_command_bus,
            $mock_filesystem_service,
            $mock_finder,
            new NullLogger
        );

        $fixture->setFixtureData($fixture_file);

        $mock_fixture_target = Mockery::mock(FixtureTargetInterface::CLASS);
        $mock_fixture_target->shouldReceive('isActivated')->once()->andReturnTrue();

        $fixture->execute($mock_fixture_target);
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testExecuteNotActivated()
    {
        $mock_command_bus = Mockery::mock(CommandBusInterface::CLASS);
        $mock_filesystem_service = Mockery::mock(FilesystemServiceInterface::CLASS);
        $mock_finder = Mockery::mock(Finder::CLASS);

        $fixture = new MockFixture(
            $this->aggregate_root_type_map,
            $mock_command_bus,
            $mock_filesystem_service,
            $mock_finder,
            new NullLogger
        );

        $mock_fixture_target = Mockery::mock(FixtureTargetInterface::CLASS);
        $mock_fixture_target->shouldReceive('isActivated')->once()->andReturnFalse();
        $mock_fixture_target->shouldReceive('getName')->once()->andReturn('TestFixtureTarget');

        $fixture->execute($mock_fixture_target);
    } // @codeCoverageIgnore

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testExecuteInvalid()
    {
        $mock_command_bus = Mockery::mock(CommandBusInterface::CLASS);
        $mock_filesystem_service = Mockery::mock(FilesystemServiceInterface::CLASS);
        $mock_finder = Mockery::mock(Finder::CLASS);
        $mock_logger = Mockery::mock(LoggerInterface::CLASS);
        $mock_logger->shouldReceive('error')->once();

        $fixture = new MockFixture(
            $this->aggregate_root_type_map,
            $mock_command_bus,
            $mock_filesystem_service,
            $mock_finder,
            $mock_logger
        );

        $fixture->setFixtureData('test_fixture_error_001.json');

        $mock_fixture_target = Mockery::mock(FixtureTargetInterface::CLASS);
        $mock_fixture_target->shouldReceive('isActivated')->once()->andReturnTrue();

        $fixture->execute($mock_fixture_target);
    } // @codeCoverageIgnore

    public function testExecuteWithCopiedFiles()
    {
        $this->markTestIncomplete('Test copying of files to temp location');
    }

    /**
     * @codeCoverageIgnore
     */
    public function provideFixtureData()
    {
        $fixtures = [];
        foreach (glob(__DIR__ . '/Fixture/test_fixture_data_*.json') as $file) {
            $fixtures[] = [
                basename($file),
                str_replace('.json', '.php', $file)
            ];
        }
        return $fixtures;
    }
}
