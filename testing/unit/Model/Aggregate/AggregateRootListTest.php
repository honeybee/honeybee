<?php

namespace Honeybee\Tests\Model\Aggregate;

use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootList;
use Honeybee\Model\Aggregate\AggregateRootMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Trellis\EntityType\Attribute\EntityList\EntityList;

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
        $agg_root = Mockery::mock(AggregateRootInterface::CLASS);
        $aggregate_root_list = new AggregateRootList([ $agg_root ]);

        $this->assertInstanceOf(EntityList::CLASS, $aggregate_root_list);
        $this->assertCount(1, $aggregate_root_list);
        $this->assertEquals([ $agg_root ], $aggregate_root_list->getItems());
    }

    /**
     * @expectedException Trellis\Exception
     */
    public function testGetItemImplementorWithNotMatching()
    {
        $aggregate_root_list = new AggregateRootList([ new \stdClass ]);
    } // @codeCoverageIgnore

    public function testToMap()
    {
        $projection = Mockery::mock(AggregateRootInterface::CLASS);
        $projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('ar1');
        $aggregate_root_list = new AggregateRootList([ $projection ]);

        $aggregate_root_map = $aggregate_root_list->toMap();
        $this->assertInstanceOf(AggregateRootMap::CLASS, $aggregate_root_map);
        $this->assertCount(1, $aggregate_root_map);
        $this->assertEquals([ 'ar1' ], $aggregate_root_map->getKeys());
        $this->assertEquals([ 'ar1' => $projection ], iterator_to_array($aggregate_root_map));
    }
}
