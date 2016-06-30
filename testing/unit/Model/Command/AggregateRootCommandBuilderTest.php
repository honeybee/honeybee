<?php

namespace Honeybee\Tests\Model\Command;

use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Honeybee\Model\Command\EmbeddedEntityCommandBuilder;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType  as AuthorProjectionType;
use Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor\CreateAuthorCommand;
use Honeybee\Tests\Fixture\BookSchema\Task\ModifyAuthor\ModifyAuthorCommand;
use Honeybee\Tests\TestCase;
use Shrink0r\Monatic\Result;
use Shrink0r\Monatic\Success;
use Shrink0r\Monatic\Error;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Workflux\StateMachine\StateMachineInterface;
use Mockery;
use Honeybee\EntityInterface;

class AggregateRootCommandBuilderTest extends TestCase
{
    const AGGREGATE_ROOT_IDENTIFIER = 'honeybee.fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';

    public function provideCreateCommands()
    {
        return include __DIR__ . '/Fixture/create_commands.php';
    }

    public function provideModifyCommands()
    {
        return include __DIR__ . '/Fixture/modify_commands.php';
    }

    /**
     * @dataProvider provideCreateCommands
     */
    public function testBuildCreateCommand(array $payload, array $expected_command)
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $author_type = new AuthorType($state_machine);

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

    public function testBuildCreateCommandWithInvalidValues()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $author_type = new AuthorType($state_machine);

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues([ 'firstname' => 123, 'lastname' => 456 ])
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
                ]
            ],
            $build_result->get()
        );
    }

    public function testBuildCreateCommandWithInvalidEmbeddedCommands()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $author_type = new AuthorType($state_machine);

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues([
                'firstname' => 123,
                'lastname' => 'Gosh',
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
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $author_type = new AuthorType($state_machine);

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder->build();
    }

    /**
     * @dataProvider provideModifyCommands
     */
    public function testBuildModifyCommand(array $projection, array $payload, array $expected_command)
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $author_type = new AuthorType($state_machine);
        $projection_type = new AuthorProjectionType($state_machine);
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
        $this->assertArraySubset($expected_command, $result->toArray());
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testModifyCommandWithInvalidEntity()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $author_type = new AuthorType($state_machine);

        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);
        $build_result = $builder
            ->fromEntity(Mockery::mock(EntityInterface::CLASS))
            ->withValues([ 'firstname' => 'Amitav', 'lastname' => 'Gosh' ])
            ->build();
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testModifyCommandWithMissingIdentifier()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $author_type = new AuthorType($state_machine);

        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues([ 'firstname' => 'Amitav', 'lastname' => 'Gosh' ])
            ->build();
    }
}
