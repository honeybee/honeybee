<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Object;

class NavigationGroup extends Object implements NavigationGroupInterface
{
    protected $name;

    protected $navigation_item_list;

    public function __construct($name, NavigationItemList $navigation_item_list)
    {
        $this->name = $name;
        $this->navigation_item_list = $navigation_item_list;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNavigationItems()
    {
        return $this->navigation_item_list;
    }
}
