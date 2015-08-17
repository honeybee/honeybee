<?php

namespace Honeybee\Ui\ViewTemplate\Part;

interface CellInterface
{
    public function getCss();

    public function getGroupList();

    public function getGroup($name);
}
