<?php

namespace Honeybee\Tests\Model\Aggregate;

use Honeybee\Tests\TestCase;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Tests\Fixture\BookSchema\Model\Book\BookType;
use Honeybee\Tests\Fixture\BookSchema\Model\Publication\PublicationType;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\Author;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Model\Publisher\PublisherType;
use Workflux\StateMachine\StateMachineInterface;
use Mockery;

class AggregateRootTypeTest extends TestCase
{
    /**
     * @dataProvider provideDefaultAttributeFixture
     */
    public function testGetAttributes(AggregateRootTypeInterface $aggregate_root_type, array $expected_attribute_names)
    {
        $actual_attribute_names = $aggregate_root_type->getAttributes()->getKeys();

        $this->assertEquals($expected_attribute_names, $actual_attribute_names);
    }

    public function testAggregateRootCreation()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $aggregate_root_type = new AuthorType($state_machine);

        $aggregate_root = $aggregate_root_type->createEntity();

        $this->assertInstanceOf(Author::CLASS, $aggregate_root);
        $this->assertTrue($aggregate_root->isValid());
    }

    public function provideDefaultAttributeFixture()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);

        $honeybee_default_attributes = [
            'identifier',
            'revision',
            'uuid',
            'language',
            'version',
            'workflow_state',
            'workflow_parameters'
        ];

        return [
            [
                'aggregate_root_type' => new AuthorType($state_machine),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'firstname', 'lastname', 'blurb', 'products', 'books' ]
                )
            ],
            [
                'aggregate_root_type' => new BookType($state_machine),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'title', 'description' ]
                )
            ],
            [
                'aggregate_root_type' => new PublisherType($state_machine),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'name', 'description' ]
                )
            ],
            [
                'aggregate_root_type' => new PublicationType($state_machine),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'year', 'description' ]
                )
            ]
        ];
    }
}
