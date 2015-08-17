<?php

namespace Honeybee\Tests\Projection\Resource;

use Honeybee\Tests\Projection\Resource\Fixtures\Author\AuthorType;
use Honeybee\Tests\TestCase;
use Workflux\Builder\XmlStateMachineBuilder;

class ResourceTest extends TestCase
{
    protected $resource_type = AuthorType::CLASS;

    protected $data = [
        'firstname' => 'Mark',
        'lastname' => 'Twain'
    ];

    public function setUp()
    {
        $resource_type = new $this->resource_type($this->getDefaultStateMachine());
        $this->resource = $resource_type->createEntity($this->data);
    }

    public function testInterface()
    {
        $this->assertEquals('Mark', $this->resource->getFirstname());
        $this->assertEquals('Twain', $this->resource->getLastname());
        $this->assertNotEmpty($this->resource->getCreatedAt());
        $this->assertNotEmpty($this->resource->getModifiedAt());
        $this->assertEquals('honeybee-cmf.resource_fixtures.author-0', $this->resource->getShortIdentifier());
        $this->assertNull($this->resource->getUuid());
        // UUID self-generated
        $this->assertNull($this->resource->getUuid());
        $this->assertEquals(0, $this->resource->getRevision());
        $this->assertEquals('de_DE', $this->resource->getLanguage());
        $this->assertEquals(0, $this->resource->getShortId());
        $this->assertEmpty($this->resource->getWorkflowState());
        $this->assertInternalType('array', $this->resource->getWorkflowParameters());
        $this->assertEmpty($this->resource->getWorkflowParameters());
        $this->assertInternalType('string', $this->resource->__toString());
        $this->assertEquals($this->resource->getIdentifier(), $this->resource->__toString());
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
