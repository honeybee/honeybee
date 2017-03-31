<?php

namespace Honeybee\Tests\Infrastructure\Command;

use Honeybee\Infrastructure\Command\CommandEnricher;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Command\Metadata;
use Honeybee\Infrastructure\Command\MetadataEnricherInterface;
use Honeybee\Tests\TestCase;
use Mockery;

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
        $input_data = [ 'test' => 'value' ];
        $enriched_data = [ 'test' => 'value', 'enriched' => 'data' ];
        $input_metadata = new Metadata($input_data);
        $ouput_metadata = new Metadata($enriched_data);

        $mock_enricher = Mockery::mock(MetadataEnricherInterface::CLASS);
        $mock_enricher->shouldReceive('enrich')
            ->once()
            ->with(Mockery::on(
                function ($metadata) use ($input_metadata) {
                    $this->assertEquals($input_metadata, $metadata);
                    return true;
                }
            ))
            ->andReturn($ouput_metadata);

        $mock_command_copy = Mockery::mock(CommandInterface::CLASS);
        $mock_command_copy->shouldReceive('getMetadata')->once()->withNoArgs()->andReturn($enriched_data);

        $mock_command = Mockery::mock(CommandInterface::CLASS);
        $mock_command->shouldReceive('getMetadata')->once()->withNoArgs()->andReturn($input_data);
        $mock_command->shouldReceive('withMetadata')
            ->once()
            ->with(Mockery::on(
                function ($metadata) use ($ouput_metadata) {
                    $this->assertEquals($ouput_metadata, $metadata);
                    return true;
                }
            ))
            ->andReturn($mock_command_copy);

        $command_enricher = new CommandEnricher([ $mock_enricher ]);
        $enriched_command = $command_enricher->enrich($mock_command);

        $this->assertInstanceOf(CommandInterface::CLASS, $enriched_command);
        $this->assertEquals($enriched_data, $enriched_command->getMetadata());
    }
}
