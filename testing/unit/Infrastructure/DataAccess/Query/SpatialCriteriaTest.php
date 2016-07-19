<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\Comparison\In;
use Honeybee\Infrastructure\DataAccess\Query\Geography\GeoHash;
use Honeybee\Infrastructure\DataAccess\Query\Geography\GeoPoint;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Box;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Circle;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Point;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Polygon;
use Honeybee\Infrastructure\DataAccess\Query\SpatialCriteria;
use Honeybee\Tests\TestCase;

class SpatialCriteriaTest extends TestCase
{
    public function testConstructWithCircle()
    {
        $criteria = new SpatialCriteria(
            'location',
            new In(new Circle(new Point(-1, 2.123), '21parsecs'))
        );

        $this->assertEquals('location', $criteria->getAttributePath());
        $this->assertEquals(
            'ATTRIBUTE location SPATIAL IN CIRCLE CENTER -1,2.123 RADIUS 21parsecs',
            (string)$criteria
        );
    }

    public function testConstructWithInvertedCircle()
    {
        $criteria = new SpatialCriteria(
            'location',
            new In(new Circle(new Point(-1, 2.123), '1mile'), true)
        );

        $this->assertEquals('location', $criteria->getAttributePath());
        $this->assertEquals(
            'ATTRIBUTE location SPATIAL NOT IN CIRCLE CENTER -1,2.123 RADIUS 1mile',
            (string)$criteria
        );
    }

    public function testConstructWithPolygon()
    {
        $criteria = new SpatialCriteria(
            'location',
            new In(new Polygon([ new GeoHash('abcd'), new Point(3.14, 15.9) ]))
        );

        $this->assertEquals('location', $criteria->getAttributePath());
        $this->assertEquals('ATTRIBUTE location SPATIAL IN POLYGON abcd;3.14,15.9', (string)$criteria);
    }

    public function testConstructWithBox()
    {
        $criteria = new SpatialCriteria(
            'location',
            new In(new Box(new GeoPoint(4, 9), new Point(3.14, 15.9)))
        );

        $this->assertEquals('location', $criteria->getAttributePath());
        $this->assertEquals('ATTRIBUTE location SPATIAL IN BOX 9,4;3.14,15.9', (string)$criteria);
    }
}
