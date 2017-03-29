<?php

namespace Honeybee\Tests\Infrastructure\Fixture;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Fixture\FileSystemLoader;
use Honeybee\ServiceLocatorInterface;
use Honeybee\Tests\TestCase;
use Mockery;
use Honeybee\Infrastructure\Fixture\FixtureList;
use Symfony\Component\Finder\Finder;
use Honeybee\Infrastructure\Fixture\FixtureInterface;

class FileSystemLoaderTest extends TestCase
{
    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testLoadFixturesUnreadable()
    {
        $mock_service_locator = Mockery::mock(ServiceLocatorInterface::CLASS);
        $fs_loader = new FileSystemLoader(new ArrayConfig(['directory' => __FILE__]), $mock_service_locator);
        $fs_loader->loadFixtures();
    } //@codeCoverageIgnore

    public function testLoadFixturesNone()
    {
        $mock_service_locator = Mockery::mock(ServiceLocatorInterface::CLASS);
        $mock_iterator = Mockery::mock(\Iterator::CLASS);
        $mock_iterator->shouldReceive('rewind')->once();
        $mock_iterator->shouldReceive('valid')->andReturnFalse();
        $mock_finder = $this->makeFinder();
        $mock_finder->shouldReceive('getIterator')->once()->withNoArgs()->andReturn($mock_iterator);
        $fs_loader = new FileSystemLoader(
            new ArrayConfig(['directory' => __DIR__.'/Fixture']),
            $mock_service_locator,
            $mock_finder
        );
        $this->assertEquals(new FixtureList, $fs_loader->loadFixtures());
    }

    public function testLoadFixtures()
    {
        $mock_fixture = Mockery::mock(FixtureInterface::CLASS);
        $mock_service_locator = Mockery::mock(ServiceLocatorInterface::CLASS);
        $mock_service_locator->shouldReceive('createEntity')->once()->with(
            'Honeybee\Tests\Infrastructure\Fixture\Fixture\Fixture_20170101125959_MockFixture',
            [':state' => ['name' => 'mock_fixture', 'version' => '20170101125959']]
        )->andReturn($mock_fixture);
        $mock_iterator = Mockery::mock(\Iterator::CLASS);
        $mock_iterator->shouldReceive('rewind')->once();
        $mock_iterator->shouldReceive('valid')->twice()->andReturns(true, false);
        $mock_iterator->shouldReceive('current')->once()->andReturn(
            new \SplFileInfo(__DIR__.'/Fixture/Fixture_20170101125959_MockFixture.php')
        );
        $mock_iterator->shouldReceive('next');
        $mock_finder = $this->makeFinder();
        $mock_finder->shouldReceive('getIterator')->once()->withNoArgs()->andReturn($mock_iterator);
        $fs_loader = new FileSystemLoader(
            new ArrayConfig(['directory' => __DIR__.'/Fixture']),
            $mock_service_locator,
            $mock_finder
        );

        $fixture_list = $fs_loader->loadFixtures();
        $this->assertInstanceOf(FixtureList::CLASS, $fixture_list);
        $this->assertCount(1, $fixture_list);
        $this->assertSame($mock_fixture, $fixture_list->getFirst());
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testLoadFixturesInvalidClass()
    {
        $mock_fixture = Mockery::mock(FixtureInterface::CLASS);
        $mock_service_locator = Mockery::mock(ServiceLocatorInterface::CLASS);
        $mock_iterator = Mockery::mock(\Iterator::CLASS);
        $mock_iterator->shouldReceive('rewind')->once();
        $mock_iterator->shouldReceive('valid')->once()->andReturnTrue();
        $mock_iterator->shouldReceive('current')->once()->andReturn(
            new \SplFileInfo(__DIR__.'/FileSystemLoaderTest.php')
        );
        $mock_iterator->shouldReceive('next');
        $mock_finder = $this->makeFinder('FileSystemLoaderTest.php', __DIR__);
        $mock_finder->shouldReceive('getIterator')->once()->withNoArgs()->andReturn($mock_iterator);
        $fs_loader = new FileSystemLoader(
            new ArrayConfig(['directory' => __DIR__, 'pattern' => 'FileSystemLoaderTest.php']),
            $mock_service_locator,
            $mock_finder
        );

        $fs_loader->loadFixtures();
    } //@codeCoverageIgnore

    private function makeFinder($name = '*.php', $in = __DIR__.'/Fixture')
    {
        $mock_finder = Mockery::mock(Finder::CLASS);
        $mock_finder->shouldReceive('create')->once()->withNoArgs()->andReturnSelf();
        $mock_finder->shouldReceive('files')->once()->withNoArgs()->andReturnSelf();
        $mock_finder->shouldReceive('name')->once()->with($name)->andReturnSelf();
        $mock_finder->shouldReceive('in')->once()->with($in)->andReturnSelf();
        return $mock_finder;
    }
}
