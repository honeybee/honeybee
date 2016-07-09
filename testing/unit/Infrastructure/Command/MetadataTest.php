<?php

namespace Honeybee\Tests\Infrastructure\Command;

use Honeybee\Tests\TestCase;
use Honeybee\Infrastructure\Command\Metadata;

class MetadataTest extends TestCase
{
    public function testConstruct()
    {
        $data = [ 'test' => 'value' ];
        $metadata = new Metadata($data);
        $this->assertEquals($data, $metadata->toArray());
    }
}
