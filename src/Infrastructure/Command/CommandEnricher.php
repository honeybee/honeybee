<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class CommandEnricher extends TypedList implements UniqueCollectionInterface, CommandEnricherInterface
{
    public function enrich(CommandInterface $command)
    {
        $metadata = new Metadata($command->getMetadata());

        foreach ($this->items as $metadata_enricher) {
            $metadata_enricher->enrich($metadata);
        }

        return $command->withMetadata($metadata);
    }

    protected function getItemImplementor()
    {
        return MetadataEnricherInterface::CLASS;
    }
}
