<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Tests\TestCase;
use Honeybee\Infrastructure\DataAccess\Query\RangeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\LessThan;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\GreaterThan;

class RangeCriteriaTest extends TestCase
{
    public function testConstructSingleComparison()
    {
        $criteria = new RangeCriteria('amount', new LessThan(4));

        $this->assertEquals('amount', $criteria->getAttributePath());
        $this->assertEquals('ATTRIBUTE amount RANGE LT 4', (string)$criteria);
    }

    public function testConstructMultipleComparison()
    {
        $criteria = new RangeCriteria('amount', new LessThan(4), new GreaterThan(2));

        $this->assertEquals('amount', $criteria->getAttributePath());
        $this->assertEquals('ATTRIBUTE amount RANGE LT 4 AND GT 2', (string)$criteria);
    }
}
