<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\Map;
use Honeybee\Common\Error\RuntimeError;

class Metadata extends Map
{
    public function offsetSet($offset, $value)
    {
        if (true === array_key_exists($offset, $this->items)) {
            throw new RuntimeError(sprintf('Offset "%s" is already set and cannot be overridden.', $offset));
        }

        parent::offsetSet($offset, $value);
    }
}
