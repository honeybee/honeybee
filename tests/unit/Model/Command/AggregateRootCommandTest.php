<?php

namespace Honeybee\Tests\Model\Command;

use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\ModifyAuthor\ModifyAuthorCommand;
use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType  as AuthorProjectionType;
use Honeybee\Tests\TestCase;

class AggregateRootCommandTest extends TestCase
{
    const AGGREGATE_ROOT_IDENTIFIER = 'honeybee.fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';

    public function testToStringMethodOfAggregateRootCommand()
    {
        $author_type = new AuthorType();
        $projection_type = new AuthorProjectionType();
        $projection = $projection_type->createEntity([
            'identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
            'revision' => 5,
            'firstname' => 'Me',
            'lastname' => 'Myself'
        ]);

        $builder = new AggregateRootCommandBuilder(new AuthorType, ModifyAuthorCommand::CLASS);
        $build_result = $builder
            ->fromEntity($projection)
            ->withValues(['firstname' => 'another'])
            ->build();

        $command = $build_result->get();
        $this->assertInstanceOf(ModifyAuthorCommand::CLASS, $command);
        $this->assertEquals(
            ModifyAuthorCommand::CLASS.' for '.self::AGGREGATE_ROOT_IDENTIFIER.' with known_revision 5',
            (string)$command
        );
    }
}
