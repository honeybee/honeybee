<?php

namespace Honeybee\Tests\Model\Aggregate;

use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootList;
use Honeybee\Model\Aggregate\AggregateRootMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Trellis\Runtime\Entity\EntityMap;

class AggregateRootMapTest extends TestCase
{
    public function testWithEmpty()
    {
        $aggregate_root_map = new AggregateRootMap;

        $this->assertInstanceOf(EntityMap::CLASS, $aggregate_root_map);
        $this->assertCount(0, $aggregate_root_map);
    }

    public function testGetItemImplementor()
    {
        $aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $aggregate_root->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('ar1');
        $aggregate_root_map = new AggregateRootMap([ $aggregate_root ]);

        $this->assertInstanceOf(EntityMap::CLASS, $aggregate_root_map);
        $this->assertCount(1, $aggregate_root_map);
        $this->assertEquals([ 'ar1' ], $aggregate_root_map->getKeys());
        $this->assertEquals([ $aggregate_root ], $aggregate_root_map->getValues());
    }

    /**
     * @expectedException Trellis\Common\Error\InvalidTypeException
     */
    public function testGetItemImplementorWithNotMatching()
    {
        $aggregate_root_map = new AggregateRootMap([ new \stdClass ]);
    }

    public function testToList()
    {
        $aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $aggregate_root->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('ar1');
        $aggregate_root->shouldReceive('addEntityChangedListener')->once()->with(Mockery::on(
            function ($listener) {
                $this->assertInstanceOf(AggregateRootList::CLASS, $listener);
                return true;
            }
        ));
        $aggregate_root_map = new AggregateRootMap([ $aggregate_root ]);

        $aggregate_root_list = $aggregate_root_map->toList();
        $this->assertInstanceOf(AggregateRootList::CLASS, $aggregate_root_list);
        $this->assertCount(1, $aggregate_root_list);
        $this->assertEquals([ $aggregate_root ], $aggregate_root_list->getItems());
    }
}
