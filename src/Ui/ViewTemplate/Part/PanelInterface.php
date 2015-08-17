<?php

namespace Honeybee\Ui\ViewTemplate\Part;

interface PanelInterface extends NamedItemInterface
{
    public function getCss();

    public function getRowList();
}
