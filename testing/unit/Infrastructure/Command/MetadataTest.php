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

    /**
     * @expectedException Trellis\Common\Error\RuntimeException
     */
    public function testAppendWithKeyCollision()
    {
        $data = [ 'test' => 'value' ];
        $colliding_data = [ 'test' => 'immutable' ];
        $metadata = new Metadata($data);
        $colliding_metadata = new Metadata($colliding_data);
        $metadata->append($colliding_metadata);
    }

    /**
     * @expectedException Trellis\Common\Error\RuntimeException
     */
    public function testSetItemWithKeyCollision()
    {
        $data = [ 'test' => 'value' ];
        $metadata = new Metadata($data);
        $metadata->setItem('test', 'immutable');
    }
}
