<?php

namespace Honeybee\Tests\Projection;

use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType;
use Honeybee\Tests\TestCase;

class ProjectionTest extends TestCase
{
    protected $projection_type = AuthorType::CLASS;

    protected $data = [
        'firstname' => 'Mark',
        'lastname' => 'Twain'
    ];

    public function setUp()
    {
        $projection_type = new $this->projection_type();
        $this->projection = $projection_type->createEntity($this->data);
    }

    public function testInterface()
    {
        $this->assertEquals('Mark', $this->projection->getFirstname());
        $this->assertEquals('Twain', $this->projection->getLastname());
        $this->assertNull($this->projection->getCreatedAt());
        $this->assertNull($this->projection->getModifiedAt());
        $this->assertNull($this->projection->getUuid());
        // UUID self-generated
        $this->assertNull($this->projection->getUuid());
        $this->assertEquals(0, $this->projection->getRevision());
        $this->assertEquals('de_DE', $this->projection->getLanguage());
        $this->assertEmpty($this->projection->getWorkflowState());
        $this->assertInternalType('array', $this->projection->getWorkflowParameters());
        $this->assertEmpty($this->projection->getWorkflowParameters());
        $this->assertInternalType('string', $this->projection->__toString());
        $this->assertEquals($this->projection->getIdentifier(), $this->projection->__toString());
    }
}
