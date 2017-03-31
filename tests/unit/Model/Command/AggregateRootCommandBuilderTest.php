<?php

namespace Honeybee\Tests\Model\Command;

use Honeybee\EntityInterface;
use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Model\Publication\PublicationType;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\CreatePublication\CreatePublicationCommand;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\ModifyAuthor\ModifyAuthorCommand;
use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType  as AuthorProjectionType;
use Honeybee\Tests\TestCase;
use Mockery;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Result;
use Shrink0r\Monatic\Success;

class AggregateRootCommandBuilderTest extends TestCase
{
    const AGGREGATE_ROOT_IDENTIFIER = 'honeybee.fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';

    /**
     * @codeCoverageIgnore
     */
    public function provideCreateCommands()
    {
        return include __DIR__ . '/Fixture/create_commands.php';
    }

    /**
     * @codeCoverageIgnore
     */
    public function provideModifyCommands()
    {
        return include __DIR__ . '/Fixture/modify_commands.php';
    }

    /**
     * @dataProvider provideCreateCommands
     */
    public function testBuildCreateCommand(array $payload, array $expected_command)
    {
        $author_type = new AuthorType();

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues($payload['author'])
            ->build();

        $this->assertInstanceOf(Result::CLASS, $build_result);
        $this->assertInstanceOf(Success::CLASS, $build_result);
        $result = $build_result->get();
        $this->assertInstanceOf(CreateAuthorCommand::CLASS, $result);
        $this->assertArraySubset($expected_command, $result->toArray());
    }

    public function testBuildCreateCommandWithEmptyValues()
    {
        $publication_type = new PublicationType();

        $builder = new AggregateRootCommandBuilder($publication_type, CreatePublicationCommand::CLASS);
        $build_result = $builder
            ->withValues([])
            ->build();

        $this->assertInstanceOf(Result::CLASS, $build_result);
        $this->assertInstanceOf(Success::CLASS, $build_result);
        $result = $build_result->get();
        $this->assertInstanceOf(CreatePublicationCommand::CLASS, $result);
        $this->assertArraySubset(
            [
                '@type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Task\CreatePublication\CreatePublicationCommand',
                'values' => [],
                'aggregate_root_type' => 'honeybee_cmf.aggregate_fixtures.publication',
                'embedded_entity_commands' => [],
                'metadata' => []
            ],
            $result->toArray()
        );
    }

    public function testBuildCreateCommandWithInvalidValues()
    {
        $author_type = new AuthorType();

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues([
                'firstname' => 123,
                'lastname' => 456,
                'email' => 'invalid'
            ])
            ->build();

        $this->assertInstanceOf(Result::CLASS, $build_result);
        $this->assertInstanceOf(Error::CLASS, $build_result);
        $this->assertEquals(
            [
                'firstname' => [
                    [
                        'path' => 'firstname',
                        'incidents' => [ 'non_string_value' => [ 'value' => 123 ] ]
                    ]
                ],
                'lastname' => [
                    [
                        'path' => 'lastname',
                        'incidents' => [ 'non_string_value' => [ 'value' => 456 ] ]
                    ]
                ],
                'email' => [
                    [
                        'path' => 'email',
                        'incidents' => [ 'invalid_format' => [ 'reason' => 'No Domain part' ] ]
                    ]
                ]
            ],
            $build_result->get()
        );
    }

    public function testBuildCreateCommandWithInvalidCommands()
    {
        $author_type = new AuthorType();

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues([
                'firstname' => 123,
                // missing mandatory email & lastname
                'products' => [
                    [
                        '@type' => 'highlight',
                        'title' => 456,
                        'description' => 789
                    ],
                    [
                        '@type' => 'highlight',
                        'title' => 890,
                        'description' => 321
                    ],
                    [
                        '@type' => 'highlight',
                        // missing mandatory title
                        'description' => 222
                    ],
                    [
                        // missing @type
                        'title' => 'hello'
                    ],
                    [
                        // invalid @type
                        '@type' => 'brexit',
                        'title' => 'goodbye'
                    ]
                ]
            ])
            ->build();

        $this->assertInstanceOf(Result::CLASS, $build_result);
        $this->assertInstanceOf(Error::CLASS, $build_result);
        $this->assertEquals(
            [
                'firstname' => [
                    [
                        'path' => 'firstname',
                        'incidents' => [ 'non_string_value' => [ 'value' => 123 ] ]
                    ]
                ],
                'email' => [
                    [
                        'path' => 'email',
                        'incidents' => [ 'mandatory' => [ 'reason' => 'missing' ] ]
                    ]
                ],
                'lastname' => [
                    [
                        'path' => 'lastname',
                        'incidents' => [ 'mandatory' => [ 'reason' => 'missing' ] ]
                    ]
                ],
                'products.0.title' => [
                    [
                        'path' => 'products.highlight.title',
                        'incidents' => [ 'non_string_value' => [ 'value' => 456 ] ]
                    ]
                ],
                'products.0.description' => [
                    [
                        'path' => 'products.highlight.description',
                        'incidents' => [ 'non_string_value' => [ 'value' => 789 ] ]
                    ]
                ],
                'products.1.title' => [
                    [
                        'path' => 'products.highlight.title',
                        'incidents' => [ 'non_string_value' => [ 'value' => 890 ] ]
                    ]
                ],
                'products.1.description' => [
                    [
                        'path' => 'products.highlight.description',
                        'incidents' => [ 'non_string_value' => [ 'value' => 321 ] ]
                    ]
                ],
                'products.2.title' => [
                    [
                        'path' => 'products.highlight.title',
                        'incidents' => [ 'mandatory' => [ 'reason' => 'missing' ] ]
                    ]
                ],
                'products.2.description' => [
                    [
                        'path' => 'products.highlight.description',
                        'incidents' => [ 'non_string_value' => [ 'value' => 222 ] ]
                    ]
                ],
                'products.3.@type' => [
                    [
                        'path' => 'products',
                        'incidents' => [ 'invalid_type' => [ 'reason' => 'missing' ] ]
                    ]
                ],
                'products.4.@type' => [
                    [
                        'path' => 'products',
                        'incidents' => [ 'invalid_type' => [ 'reason' => 'unknown' ] ]
                    ]
                ]
            ],
            $build_result->get()
        );
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testCreateCommandWithMissingValues()
    {
        $author_type = new AuthorType();

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder->build();
    } // @codeCoverageIgnore

    /**
     * @dataProvider provideModifyCommands
     */
    public function testBuildModifyCommand(array $projection, array $payload, array $expected_command)
    {
        $author_type = new AuthorType();
        $projection_type = new AuthorProjectionType();
        $projection = $projection_type->createEntity($projection);

        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);
        $build_result = $builder
            ->fromEntity($projection)
            ->withValues($payload['author'])
            ->build();

        $this->assertInstanceOf(Result::CLASS, $build_result);
        $this->assertInstanceOf(Success::CLASS, $build_result);
        $result = $build_result->get();
        $this->assertInstanceOf(ModifyAuthorCommand::CLASS, $result);
        $result_as_array = $result->toArray();
        $this->assertEquals($expected_command, $this->filterUuids($result_as_array));
    }

    protected function filterUuids(array &$payload)
    {
        if (isset($payload['uuid'])) {
            unset($payload['uuid']);
        }
        foreach ($payload as $key => &$value) {
            if (is_array($value)) {
                $this->filterUuids($value);
            }
        }
        return $payload;
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testModifyCommandWithInvalidEntity()
    {
        $author_type = new AuthorType();

        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);
        $build_result = $builder->fromEntity(Mockery::mock(EntityInterface::CLASS));
    } // @codeCoverageIgnore

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testModifyCommandWithMissingIdentifier()
    {
        $author_type = new AuthorType();

        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);
        $build_result = $builder
            ->withKnownRevision(1)
            ->withValues([
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'email' => 'test@honeybee.com'
            ])
            ->build();
    } // @codeCoverageIgnore
}
