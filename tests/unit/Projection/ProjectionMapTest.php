<?php

namespace Honeybee\Tests\Projection;

use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionList;
use Honeybee\Projection\ProjectionMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Trellis\Runtime\Entity\EntityMap;

class ProjectionMapTest extends TestCase
{
    public function testWithEmpty()
    {
        $projection_map = new ProjectionMap;

        $this->assertInstanceOf(EntityMap::CLASS, $projection_map);
        $this->assertCount(0, $projection_map);
    }

    public function testGetItemImplementor()
    {
        $projection = Mockery::mock(ProjectionInterface::CLASS);
        $projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('projection1');
        $projection_map = new ProjectionMap([ $projection ]);

        $this->assertInstanceOf(EntityMap::CLASS, $projection_map);
        $this->assertCount(1, $projection_map);
        $this->assertEquals([ 'projection1' ], $projection_map->getKeys());
        $this->assertEquals([ $projection ], $projection_map->getValues());
    }

    /**
     * @expectedException Trellis\Common\Error\InvalidTypeException
     */
    public function testGetItemImplementorWithNotMatching()
    {
        $projection_map = new ProjectionMap([ new \stdClass ]);
    } // @codeCoverageIgnore

    public function testToList()
    {
        $projection = Mockery::mock(ProjectionInterface::CLASS);
        $projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('projection1');
        $projection->shouldReceive('addEntityChangedListener')->once()->with(Mockery::on(
            function ($listener) {
                $this->assertInstanceOf(ProjectionList::CLASS, $listener);
                return true;
            }
        ));
        $projection_map = new ProjectionMap([ $projection ]);

        $projection_list = $projection_map->toList();
        $this->assertInstanceOf(ProjectionList::CLASS, $projection_list);
        $this->assertCount(1, $projection_list);
        $this->assertEquals([ $projection ], $projection_list->getItems());
    }
}
