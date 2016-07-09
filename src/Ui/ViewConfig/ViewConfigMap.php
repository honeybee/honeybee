<?php

namespace Honeybee\Ui\ViewConfig;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class ViewConfigMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $view_configs = [])
    {
        parent::__construct(ViewConfigInterface::CLASS, $view_configs);
    }
}
