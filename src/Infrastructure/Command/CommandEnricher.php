<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class CommandEnricher extends TypedList implements UniqueItemInterface, CommandEnricherInterface
{
    public function __construct(array $enrichers = [])
    {
        parent::__construct(MetadataEnricherInterface::CLASS, $enrichers);
    }

    public function enrich(CommandInterface $command)
    {
        $metadata = new Metadata($command->getMetadata());

        foreach ($this->items as $metadata_enricher) {
            $metadata = $metadata_enricher->enrich($metadata);
        }

        return $command->withMetadata($metadata);
    }
}
