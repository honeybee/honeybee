<?php

namespace Honeybee\Tests\Infrastructure\Fixture;

use Honeybee\Infrastructure\Fixture\FixtureList;
use Honeybee\Infrastructure\Fixture\FixtureLoaderInterface;
use Honeybee\Infrastructure\Fixture\FixtureTarget;
use Honeybee\Tests\TestCase;
use Mockery;

class FixtureTargetTest extends TestCase
{
    public function testGetName()
    {
        $mock_filesystem_loader = Mockery::mock(FixtureLoaderInterface::CLASS);
        $fixture_target = new FixtureTarget('mock_target', true, $mock_filesystem_loader);

        $this->assertEquals('mock_target', $fixture_target->getName());
    }

    public function testIsActivated()
    {
        $mock_filesystem_loader = Mockery::mock(FixtureLoaderInterface::CLASS);
        $fixture_target = new FixtureTarget('mock_target', true, $mock_filesystem_loader);

        $this->assertTrue($fixture_target->isActivated());
    }

    public function testIsActivatedNotActivated()
    {
        $mock_filesystem_loader = Mockery::mock(FixtureLoaderInterface::CLASS);
        $fixture_target = new FixtureTarget('mock_target', 'false', $mock_filesystem_loader);

        $this->assertFalse($fixture_target->isActivated());
    }

    public function testGetFixtureList()
    {
        $fixture_list = new FixtureList;
        $mock_filesystem_loader = Mockery::mock(FixtureLoaderInterface::CLASS);
        $mock_filesystem_loader->shouldReceive('loadFixtures')->once()->withNoArgs()->andReturn($fixture_list);
        $fixture_target = new FixtureTarget('mock_target', 'false', $mock_filesystem_loader);

        $this->assertSame($fixture_list, $fixture_target->getFixtureList());
    }
}
