<?php

namespace Honeybee\Tests\Model\Command;

use Assert\InvalidArgumentException;
use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;
use Honeybee\Tests\Model\Task\CreateAuthor\CreateAuthorCommand;
use Honeybee\Tests\Model\Task\ModifyAuthor\ModifyAuthorCommand;
use Honeybee\Tests\TestCase;
use Workflux\Builder\XmlStateMachineBuilder;

class AggregateCommandBuilderTest extends TestCase
{
    const AGGREGATE_ROOT_IDENTIFIER = 'honeybee.fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';

    public function testBuildCreateCommand()
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());
        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);

        $create_command = $builder
            ->withValues([ 'firstname' => 'Amitav', 'lastname' => 'Gosh' ])
            ->build();

        $this->assertInstanceOf(CreateAuthorCommand::CLASS, $create_command);
    }

    public function testCreateCommandWithMissingValues()
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());
        $builder = new AggregateRootCommandBuilder($author_type, CreateAuthorCommand::CLASS);

        $this->setExpectedException(InvalidArgumentException::CLASS);

        $builder->build();
    }

    public function testBuildModifyCommand()
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());
        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);

        $modify_command = $builder
            ->withAggregateRootIdentifier(self::AGGREGATE_ROOT_IDENTIFIER)
            ->withKnownRevision(4)
            ->withValues([ 'firstname' => 'Amitav', 'lastname' => 'Gosh' ])
            ->build();

        $this->assertInstanceOf(ModifyAuthorCommand::CLASS, $modify_command);
    }

    public function testModifyCommandWithMissingRevision()
    {
        $author_type = new AuthorType($this->getDefaultStateMachine());
        $builder = new AggregateRootCommandBuilder($author_type, ModifyAuthorCommand::CLASS);

        $modify_command = $builder
            ->withAggregateRootIdentifier(self::AGGREGATE_ROOT_IDENTIFIER)
            ->withValues([ 'firstname' => 'Amitav', 'lastname' => 'Gosh' ]);

        $this->setExpectedException(InvalidArgumentException::CLASS);

        $builder->build();
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