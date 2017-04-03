<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\CustomCriteria;
use Honeybee\Tests\TestCase;

class CustomCriteriaTest extends TestCase
{
    public function testCustomQueryPart()
    {
        $custom = [ 'term' => 'asdf' ];
        $criteria = new CustomCriteria($custom);

        $this->assertEquals($custom, $criteria->getQueryPart());
        $this->assertEquals('CUSTOM PART {"term":"asdf"}', (string)$criteria);
    }

    public function testCustomSql()
    {
        $custom = ' AND (SELECT * FROM foo ORDER BY bar DESC)';
        $criteria = new CustomCriteria($custom);

        $this->assertEquals($custom, $criteria->getQueryPart());
        $this->assertEquals('CUSTOM PART "' . $custom . '"', (string)$criteria);
    }
}
