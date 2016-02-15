<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Tests\TestCase;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;

class AttributeCriteriaTest extends TestCase
{
    public function testConstruct()
    {
        $criteria = new AttributeCriteria('username', new Equals('honeybee-tester'));

        $this->assertEquals('username', $criteria->getAttributePath());
        $this->assertEquals('honeybee-tester', $criteria->getComparison()->getComparand());
        $this->assertEquals(Comparison::EQUALS, $criteria->getComparison()->getComparator());
        $this->assertEquals('ATTRIBUTE username EQ honeybee-tester', (string)$criteria);
    }
}
