<?php

namespace Honeybee\Tests\Model\Command;

use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Honeybee\Model\Command\EmbeddedEntityCommandBuilder;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;
use Honeybee\Tests\Projection\Fixtures\Author\AuthorType as AuthorProjectionType;
use Honeybee\Tests\Model\Task\CreateAuthor\CreateAuthorCommand;
use Honeybee\Tests\Model\Task\ModifyAuthor\ModifyAuthorCommand;
use Honeybee\Tests\TestCase;
use Shrink0r\Monatic\Result;
use Shrink0r\Monatic\Success;
use Shrink0r\Monatic\Error;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Workflux\Builder\XmlStateMachineBuilder;

class AggregateRootCommandBuilderTest extends TestCase
{
    const AGGREGATE_ROOT_IDENTIFIER = 'honeybee.fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';

    public function provideCreateCommands()
    {
        return include __DIR__ . '/Fixtures/create_commands.php';
    }

    public function provideModifyCommands()
    {
        return include __DIR__ . '/Fixtures/modify_commands.php';
    }

    /**
     * @dataProvider provideCreateCommands
     */
    public function testBuildCreateCommand(array $payload, array $expected_command)
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());

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
        $author_type = new AuthorType($this->getDefaultStateMachine());

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues([ 'firstname' => 123, 'lastname' => 456 ])
            ->build();

        $this->assertInstanceOf(Result::CLASS, $build_result);
        $this->assertInstanceOf(Error::CLASS, $build_result);
        $this->assertEquals(
            [
                'firstname' => [
                    [ 'non_string_value' => [ 'value' => 123 ] ]
                ],
                'lastname' => [
                   [ 'non_string_value' => [ 'value' => 456 ] ]
                ]
            ],
            $build_result->get()
        );
    }

    public function testBuildCreateCommandWithInvalidEmbeddedCommands()
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());

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
                    [ 'non_string_value' => [ 'value' => 123 ] ]
                ],
                'products.0.title' => [
                    [ 'non_string_value' => [ 'value' => 456 ] ]
                ],
                'products.0.description' => [
                    [ 'non_string_value' => [ 'value' => 789 ] ]
                ],
                'products.1.title' => [
                    [ 'non_string_value' => [ 'value' => 890 ] ]
                ],
                'products.1.description' => [
                    [ 'non_string_value' => [ 'value' => 321 ] ]
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
        $author_type = new AuthorType($this->getDefaultStateMachine());

        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);
        $build_result = $builder->build();
    }

    /**
     * @dataProvider provideModifyCommands
     */
    public function testBuildModifyCommand(array $projection, array $payload, array $expected_command)
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());
        $projection_type = new AuthorProjectionType($this->getDefaultStateMachine());
        $projection = $projection_type->createEntity($projection);

        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);
        $build_result = $builder
            ->withProjection($projection)
            ->withValues($payload['author'])
            ->build();

        $this->assertInstanceOf(Result::CLASS, $build_result);
        $this->assertInstanceOf(Success::CLASS, $build_result);
        $result = $build_result->get();
        $this->assertInstanceOf(ModifyAuthorCommand::CLASS, $result);
        $this->assertArraySubset($expected_command, $result->toArray());
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testModifyCommandWithMissingProjection()
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());

        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);
        $build_result = $builder
            ->withValues([ 'firstname' => 'Amitav', 'lastname' => 'Gosh' ])
            ->build();
    }

    protected function getDefaultStateMachine()
    {
        $workflows_file_path = dirname(__DIR__) . '/Aggregate/Fixtures/workflows.xml';
        $builder = new XmlStateMachineBuilder(
            [ 'name' => 'author_workflow_default', 'state_machine_definition' => $workflows_file_path ]
        );
        return $builder->build();
    }
}
