<?php

namespace Honeybee\Tests\Projection;

use Honeybee\Tests\Fixture\BookSchema\Projection\Author\Author;
use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType;
use Honeybee\Tests\TestCase;
use Workflux\StateMachine\StateMachineInterface;
use Mockery;

class ProjectionTest extends TestCase
{
    /**
     * @var AuthorType $author_type
     */
    protected $author_type;

    /**
     * @var Author $author
     */
    protected $author;

    protected $data = [
        'firstname' => 'Mark',
        'lastname' => 'Twain'
    ];

    public function setUp()
    {
        $this->author_type = new AuthorType(Mockery::mock(StateMachineInterface::CLASS));
        $this->author = $this->author_type->createEntity($this->data);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Author::CLASS, $this->author);
        $this->assertEquals('Mark', $this->author->getFirstname()->toNative());
        $this->assertEquals('Twain', $this->author->getLastname()->toNative());
        $this->assertTrue($this->author->getCreatedAt()->isEmpty());
        $this->assertTrue($this->author->getModifiedAt()->isEmpty());
        $this->assertTrue($this->author->getUuid()->isEmpty());
        // UUID self-generated
        $this->assertEquals(0, $this->author->getRevision()->toNative());
        $this->assertEquals('', $this->author->getLanguage()->toNative());
        $this->assertTrue($this->author->getWorkflowState()->isEmpty());
        $this->assertInternalType('array', $this->author->getWorkflowParameters()->toNative());
        $this->assertTrue($this->author->getWorkflowParameters()->isEmpty());
        $this->assertInternalType('string', $this->author->__toString());
        $this->assertEquals($this->author->getIdentifier()->toNative(), $this->author->__toString());
    }
}
