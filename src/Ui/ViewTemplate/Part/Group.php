<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Common\Object;

class Group extends Object implements GroupInterface
{
    protected $name;

    protected $css;

    protected $field_list;

    public function __construct($name, FieldList $field_list, $css = '')
    {
        $this->name = $name;
        $this->field_list = $field_list;
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

    public function getFieldList()
    {
        return $this->field_list;
    }

    public function getField($name)
    {
        return $this->field_list->getByName($name);
    }
}
