<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Object;

class NavigationGroup extends Object implements NavigationGroupInterface
{
    protected $name;

    protected $settings;

    protected $navigation_item_list;

    public function __construct($name, NavigationItemList $navigation_item_list, array $settings)
    {
        $this->name = $name;
        $this->navigation_item_list = $navigation_item_list;
        $this->settings = $settings;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getNavigationItems()
    {
        return $this->navigation_item_list;
    }
}
