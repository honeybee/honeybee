<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class GroupList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $groups = [])
    {
        parent::__construct(GroupInterface::CLASS, $groups);
    }
}
