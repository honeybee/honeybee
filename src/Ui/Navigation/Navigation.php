<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Object;

class Navigation extends Object implements NavigationInterface
{
    protected $name;

    protected $navigation_group_map;

    public function __construct($name, NavigationGroupMap $navigation_group_map)
    {
        $this->name = $name;
        $this->navigation_group_map = $navigation_group_map;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNavigationGroups()
    {
        return $this->navigation_group_map->getValues();
    }
}
