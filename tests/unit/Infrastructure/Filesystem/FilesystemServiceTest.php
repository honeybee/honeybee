<?php

namespace Honeybee\Tests\Infrastructure\Filesystem;

use Honeybee\Tests\TestCase;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Infrastructure\Filesystem\FilesystemService;

class FilesystemServiceTest extends TestCase
{
    public function testGenerateIdentifier()
    {
        $art = new AuthorType();
        $firstname_attribute = $art->getAttribute('firstname');

        // e.g. foo/bar/baz/95/8884e486-aa2f-4f91-a45f-f5f0e1c65c38
        $id = FilesystemService::generatePath($firstname_attribute);

        $expected_prefix = 'honeybee_cmf.aggregate_fixtures.author/firstname/';
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
}
