<?php

namespace Honeybee\Ui\OutputFormat;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class OutputFormatMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return OutputFormatInterface::CLASS;
    }
}
