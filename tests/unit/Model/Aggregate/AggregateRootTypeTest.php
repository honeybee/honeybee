<?php

namespace Honeybee\Tests\Model\Aggregate;

use Honeybee\Tests\TestCase;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Tests\Fixture\BookSchema\Model\Book\BookType;
use Honeybee\Tests\Fixture\BookSchema\Model\Publication\PublicationType;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\Author;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Model\Publisher\PublisherType;

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
        $aggregate_root_type = new AuthorType();

        $aggregate_root = $aggregate_root_type->createEntity();

        $this->assertInstanceOf(Author::CLASS, $aggregate_root);
        $this->assertTrue($aggregate_root->isValid());
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testAggregateRootCreationWithState()
    {
        $aggregate_root_type = new AuthorType();

        $aggregate_root = $aggregate_root_type->createEntity([ 'invalid' => 'state' ]);
    } // @codeCoverageIgnore

    /**
     * @codeCoverageIgnore
     */
    public function provideDefaultAttributeFixture()
    {
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
                'aggregate_root_type' => new AuthorType(),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'firstname', 'lastname', 'email', 'birth_date', 'blurb', 'products', 'books' ]
                )
            ],
            [
                'aggregate_root_type' => new BookType(),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'title', 'description' ]
                )
            ],
            [
                'aggregate_root_type' => new PublisherType(),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'name', 'description' ]
                )
            ],
            [
                'aggregate_root_type' => new PublicationType(),
                'expected_attribute_names' => array_merge(
                    $honeybee_default_attributes,
                    [ 'year', 'description' ]
                )
            ]
        ];
    }
}
