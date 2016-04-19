<?php

namespace Honeybee\Tests\Model\Aggregate;

use Honeybee\Tests\TestCase;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Tests\Model\Aggregate\Fixtures\Book\BookType;
use Honeybee\Tests\Model\Aggregate\Fixtures\Publication\PublicationType;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\Author;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;
use Honeybee\Tests\Model\Aggregate\Fixtures\Publisher\PublisherType;
use Workflux\Builder\XmlStateMachineBuilder;

class AggregateRootTypeTest extends TestCase
{
    /**
     * @dataProvider provideDefaultAttributeFixtures
     */
    public function testGetAttributes(AggregateRootTypeInterface $aggregate_root_type, array $expected_attribute_names)
    {
        $actual_attribute_names = $aggregate_root_type->getAttributes()->getKeys();

        $this->assertEquals($expected_attribute_names, $actual_attribute_names);
    }

    public function testAggregateRootCreation()
    {
        $aggregate_root_type = new AuthorType($this->getDefaultStateMachine());

        $aggregate_root = $aggregate_root_type->createEntity();

        $this->assertInstanceOf(Author::CLASS, $aggregate_root);
        $this->assertTrue($aggregate_root->isValid());
    }

    public function provideDefaultAttributeFixtures()
    {
        $honeybee_default_attributes = [
            'identifier',
            'revision',
            'uuid',
            'short_id',
            'language',
            'version',
            'created_at',
            'modified_at',
            'workflow_state',
            'workflow_parameters'
        ];

        return [
            [
                'aggregate_root_type' => new AuthorType($this->getDefaultStateMachine()),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'firstname', 'lastname', 'blurb', 'products', 'books' ]
                )
            ],
            [
                'aggregate_root_type' => new BookType($this->getDefaultStateMachine()),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'title', 'description' ]
                )
            ],
            [
                'aggregate_root_type' => new PublisherType($this->getDefaultStateMachine()),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'name', 'description' ]
                )
            ],
            [
                'aggregate_root_type' => new PublicationType($this->getDefaultStateMachine()),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'year', 'description' ]
                )
            ]
        ];
    }

    protected function getDefaultStateMachine()
    {
        $workflows_file_path = __DIR__ . '/Fixtures/workflows.xml';
        $workflow_builder = new XmlStateMachineBuilder(
            [
                'name' => 'author_workflow_default',
                'state_machine_definition' => $workflows_file_path
            ]
        );

        return $workflow_builder->build();
    }
}
