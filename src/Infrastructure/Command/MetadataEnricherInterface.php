<?php

namespace Honeybee\Infrastructure\Command;

interface MetadataEnricherInterface
{
    public function enrich(Metadata $metadata);
}
