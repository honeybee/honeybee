<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class FieldList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $fields = [])
    {
        parent::__construct(FieldInterface::CLASS, $fields);
    }
}
