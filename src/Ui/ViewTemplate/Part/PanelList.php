<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class PanelList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $panels = [])
    {
        parent::__construct(PanelInterface::CLASS, $panels);
    }
}
