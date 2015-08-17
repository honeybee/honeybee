<?php

namespace Honeybee\Ui\ViewTemplate;

interface ViewTemplatesContainerInterface
{
    public function getScope();

    public function getViewTemplateMap();

    public function getViewTemplateByName($name);
}
