<?php

namespace Honeybee\Tests\Projection;

use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionList;
use Honeybee\Projection\ProjectionMap;
use Honeybee\Tests\Fixture\BookSchema\Projection\Book\BookType;
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

    public function testHasKeySucceeds()
    {
        $type = new BookType;
        $map = new ProjectionMap;

        $test_entity = $type->createEntity([ 'uuid' => '2d10d19a-7aca-4d87-aa34-1ea9a5604138' ]);
        $map->setItem($test_entity->getIdentifier(), $test_entity);

        $this->assertTrue($map->hasKey($test_entity->getIdentifier()));
    }

    public function testHasItemSucceeds()
    {
        $type = new BookType;
        $map = new ProjectionMap;

        $test_entity = $type->createEntity([ 'uuid' => '2d10d19a-7aca-4d87-aa34-1ea9a5604138' ]);
        $map->setItem($test_entity->getIdentifier(), $test_entity);

        $this->assertTrue($map->hasItem($test_entity));
    }

    public function testHasKeySucceedsWhenConstructedWithItems()
    {
        $type = new BookType;
        $test_entity = $type->createEntity([ 'uuid' => '2d10d19a-7aca-4d87-aa34-1ea9a5604138' ]);
        $map = new ProjectionMap([$test_entity]);
        $this->assertTrue($map->hasKey($test_entity->getIdentifier()));
    }

    public function testHasItemSucceedsWhenConstructedWithItems()
    {
        $type = new BookType;
        $test_entity = $type->createEntity([ 'uuid' => '2d10d19a-7aca-4d87-aa34-1ea9a5604138' ]);
        $map = new ProjectionMap([$test_entity]);
        $this->assertTrue($map->hasItem($test_entity));
    }

    public function testHasItemSucceedsWhenConstructedWithItemsAndComparedWithDifferentInstanceOfSameEntity()
    {
        $type = new BookType;
        $entity = $type->createEntity([ 'uuid' => '2d10d19a-7aca-4d87-aa34-1ea9a5604138' ]);
        $other_entity = $type->createEntity([ 'uuid' => '2d10d19a-7aca-4d87-aa34-1ea9a5604138' ]);
        $map = new ProjectionMap([$entity]);
        $this->assertTrue($map->hasItem($other_entity));
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
