<?php

namespace Honeybee\Ui\OutputFormat;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class OutputFormatMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return OutputFormatInterface::CLASS;
    }
}
