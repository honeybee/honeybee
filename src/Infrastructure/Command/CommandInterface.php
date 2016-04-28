<?php

namespace Honeybee\Infrastructure\Command;

interface CommandInterface
{
    public static function getType();

    public function getUuid();

    public function getMetadata();

    public function withMetadata(Metadata $metadata);
}
