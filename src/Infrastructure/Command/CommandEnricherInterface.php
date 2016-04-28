<?php

namespace Honeybee\Infrastructure\Command;

interface CommandEnricherInterface
{
    public function enrich(CommandInterface $command);
}
