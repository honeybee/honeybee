<?php

namespace Honeybee\Tests\Infrastructure\Asset;

use Honeybee\Infrastructure\Filesystem\FilesystemService;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;
use Honeybee\Tests\TestCase;
use Workflux\Builder\XmlStateMachineBuilder;

class FilesystemServiceTest extends TestCase
{
    public function testGenerateIdentifier()
    {
        $art = new AuthorType($this->getDefaultStateMachine());
        $firstname_attribute = $art->getAttribute('firstname');

        // e.g. foo/bar/baz/95/8884e486-aa2f-4f91-a45f-f5f0e1c65c38
        $id = FilesystemService::generatePath($firstname_attribute);

        $expected_prefix = 'honeybee-cmf.aggregate_fixtures.author/firstname/';
        $this->assertStringStartsWith($expected_prefix, $id);
        $this->assertStringMatchesFormat($expected_prefix . '%x/%x-%x-%x-%x-%x', $id);
    }

    public function testGenerateIdentifierWithPrefix()
    {
        $prefix = 'foo/bar/baz';

        // e.g. foo/bar/baz/95/8884e486-aa2f-4f91-a45f-f5f0e1c65c38
        $id = FilesystemService::generatePrefixedPath($prefix);
        $this->assertStringStartsWith($prefix . '/', $id);
        $this->assertStringMatchesFormat($prefix . '/%x/%x-%x-%x-%x-%x', $id);
    }

    protected function getDefaultStateMachine()
    {
        $workflows_file_path = dirname(dirname(__DIR__)) . '/Projection/Fixtures/workflows.xml';
        $workflow_builder = new XmlStateMachineBuilder(
            [
                'name' => 'author_workflow_default',
                'state_machine_definition' => $workflows_file_path
            ]
        );

        return $workflow_builder->build();
    }
}
