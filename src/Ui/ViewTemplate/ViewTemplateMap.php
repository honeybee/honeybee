<?php

namespace Honeybee\Ui\ViewTemplate;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class ViewTemplateMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $view_templates = [])
    {
        parent::__construct(ViewTemplateInterface::CLASS, $view_templates);
    }
}
