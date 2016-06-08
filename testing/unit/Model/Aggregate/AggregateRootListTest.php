<?php

namespace Honeybee\Tests\Model\Aggregate;

use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootList;
use Honeybee\Model\Aggregate\AggregateRootMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Trellis\Runtime\Entity\EntityList;

class AggregateRootListTest extends TestCase
{
    public function testWithEmpty()
    {
        $aggregate_root_list = new AggregateRootList;

        $this->assertInstanceOf(EntityList::CLASS, $aggregate_root_list);
        $this->assertCount(0, $aggregate_root_list);
    }

    public function testGetItemImplementor()
    {
        $projection = Mockery::mock(AggregateRootInterface::CLASS);
        $projection->shouldReceive('addEntityChangedListener')->once()->with(Mockery::on(
            function ($listener) {
                $this->assertInstanceOf(AggregateRootList::CLASS, $listener);
                return true;
            }
        ));
        $aggregate_root_list = new AggregateRootList([ $projection ]);

        $this->assertInstanceOf(EntityList::CLASS, $aggregate_root_list);
        $this->assertCount(1, $aggregate_root_list);
        $this->assertEquals([ $projection ], $aggregate_root_list->getItems());
    }

    /**
     * @expectedException Trellis\Common\Error\InvalidTypeException
     */
    public function testGetItemImplementorWithNotMatching()
    {
        $aggregate_root_list = new AggregateRootList([ new \stdClass ]);
    }

    public function testToMap()
    {
        $projection = Mockery::mock(AggregateRootInterface::CLASS);
        $projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('ar1');
        $projection->shouldReceive('addEntityChangedListener')->once()->with(Mockery::on(
            function ($listener) {
                $this->assertInstanceOf(AggregateRootList::CLASS, $listener);
                return true;
            }
        ));
        $aggregate_root_list = new AggregateRootList([ $projection ]);

        $aggregate_root_map = $aggregate_root_list->toMap();
        $this->assertInstanceOf(AggregateRootMap::CLASS, $aggregate_root_map);
        $this->assertCount(1, $aggregate_root_map);
        $this->assertEquals([ 'ar1' ], $aggregate_root_map->getKeys());
        $this->assertEquals([ 'ar1' => $projection ], $aggregate_root_map->getItems());
        $this->assertEquals([ $projection ], $aggregate_root_map->getValues());
    }
}
