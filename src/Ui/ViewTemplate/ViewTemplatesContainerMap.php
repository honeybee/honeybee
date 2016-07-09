<?php

namespace Honeybee\Ui\ViewTemplate;

use Trellis\Collection\TypedMap;

class ViewTemplatesContainerMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $view_template_containers = [])
    {
        parent::__construct(ViewTemplatesContainerInterface::CLASS, $view_template_containers);
    }
}
