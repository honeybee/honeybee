<?php

namespace Honeybee\Ui\ViewTemplate\Part;

class Row implements RowInterface
{
    protected $css;

    protected $cell_list;

    public function __construct(CellList $cell_list, $css = '')
    {
        $this->cell_list = $cell_list;
        $this->css = $css;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function getCellList()
    {
        return $this->cell_list;
    }
}
