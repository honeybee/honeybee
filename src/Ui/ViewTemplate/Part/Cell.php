<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Common\Object;

class Cell implements CellInterface
{
    protected $css;

    protected $group_list;

    public function __construct(GroupList $group_list, $css = '')
    {
        $this->group_list = $group_list;
        $this->css = $css;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function getGroupList()
    {
        return $this->group_list;
    }

    public function getGroup($name)
    {
        return $this->group_list->getByName($name);
    }
}
