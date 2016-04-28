<?php

namespace Honeybee\Tests\Infrastructure\Command;

use Honeybee\Tests\TestCase;
use Honeybee\Infrastructure\Command\Metadata;
use Honeybee\Infrastructure\Command\Command;
use Honeybee\Infrastructure\Command\CommandEnricher;
use Honeybee\Infrastructure\Command\MetadataEnricherInterface;

class CommandEnricherTest extends TestCase
{
    public function testConstructEmpty()
    {
        $command_enricher = new CommandEnricher;

        $this->assertEquals(0, $command_enricher->count());
        $this->assertEquals([], $command_enricher->getItems());
        $this->assertEquals([], $command_enricher->toArray());
    }

    public function testEnrich()
    {
        $metadata = [ 'test' => 'value' ];

        $enricher = $this->getMock(MetadataEnricherInterface::CLASS, [ 'enrich' ]);
        $enricher->expects($this->once())
            ->method('enrich')
            ->with($this->equalTo(new Metadata($metadata)));

        $command = $this->getMockBuilder(Command::CLASS)
            ->setConstructorArgs([ [ 'metadata' => $metadata ] ])
            ->getMockForAbstractClass();

        $command_enricher = new CommandEnricher([ $enricher ]);
        $command_enricher->enrich($command);

        $this->assertEquals($metadata, $command->getMetadata());
    }
}
