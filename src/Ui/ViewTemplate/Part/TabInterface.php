<?php

namespace Honeybee\Ui\ViewTemplate\Part;

interface TabInterface extends NamedItemInterface
{
    public function getCss();

    public function getPanelList();
}
