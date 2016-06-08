<?php

namespace Honeybee\Tests\Projection;

use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionList;
use Honeybee\Projection\ProjectionMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Trellis\Runtime\Entity\EntityList;

class ProjectionListTest extends TestCase
{
    public function testWithEmpty()
    {
        $projection_list = new ProjectionList;

        $this->assertInstanceOf(EntityList::CLASS, $projection_list);
        $this->assertCount(0, $projection_list);
    }

    public function testGetItemImplementor()
    {
        $projection = Mockery::mock(ProjectionInterface::CLASS);
        $projection->shouldReceive('addEntityChangedListener')->once()->with(Mockery::on(
            function ($listener) {
                $this->assertInstanceOf(ProjectionList::CLASS, $listener);
                return true;
            }
        ));
        $projection_list = new ProjectionList([ $projection ]);

        $this->assertInstanceOf(EntityList::CLASS, $projection_list);
        $this->assertCount(1, $projection_list);
        $this->assertEquals([ $projection ], $projection_list->getItems());
    }

    /**
     * @expectedException Trellis\Common\Error\InvalidTypeException
     */
    public function testGetItemImplementorWithNotMatching()
    {
        $projection_list = new ProjectionList([ new \stdClass ]);
    }

    public function testToMap()
    {
        $projection = Mockery::mock(ProjectionInterface::CLASS);
        $projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('projection1');
        $projection->shouldReceive('addEntityChangedListener')->once()->with(Mockery::on(
            function ($listener) {
                $this->assertInstanceOf(ProjectionList::CLASS, $listener);
                return true;
            }
        ));
        $projection_list = new ProjectionList([ $projection ]);

        $projection_map = $projection_list->toMap();
        $this->assertInstanceOf(ProjectionMap::CLASS, $projection_map);
        $this->assertCount(1, $projection_map);
        $this->assertEquals([ 'projection1' ], $projection_map->getKeys());
        $this->assertEquals([ 'projection1' => $projection ], $projection_map->getItems());
        $this->assertEquals([ $projection ], $projection_map->getValues());
    }
}
