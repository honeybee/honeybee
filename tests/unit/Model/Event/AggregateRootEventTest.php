<?php

namespace Honeybee\Tests\Model\Event;

use Honeybee\Tests\Fixture\BookSchema\Model\Task\ModifyAuthor\AuthorModifiedEvent;
use Honeybee\Tests\TestCase;

class AggregateRootEventTest extends TestCase
{
    const AGGREGATE_ROOT_IDENTIFIER =
        'honeybee_cmf.aggregate_fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';

    const AGGREGATE_ROOT_TYPE = 'honeybee_cmf.aggregate_fixtures.author';

    public function testToString()
    {
        $event = new AuthorModifiedEvent([
            'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
            'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
            'uuid' => '39e3d80a-d700-4c1f-8bc7-0c3141b94af7',
            'seq_number' => 3,
            'data' => [
                'firstname' => 'Donnie'
            ]
        ]);
        $this->assertSame(
            'honeybee.tests.fixture.author_modified@39e3d80a-d700-4c1f-8bc7-0c3141b94af7 on '.
            'honeybee_cmf.aggregate_fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1@3',
            (string)$event
        );
    }
}
