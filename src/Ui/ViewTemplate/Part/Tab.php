<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Common\Object;

class Tab extends Object implements TabInterface
{
    protected $name;

    protected $css;

    protected $panel_list;

    public function __construct($name, PanelList $panel_list, $css = '')
    {
        $this->name = $name;
        $this->panel_list = $panel_list;
        $this->css = $css;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function getPanelList()
    {
        return $this->panel_list;
    }
}
