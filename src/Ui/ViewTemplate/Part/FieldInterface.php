<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Honeybee\Infrastructure\Config\ConfigInterface;

interface FieldInterface extends NamedItemInterface
{
    public function getCss();

    public function getRenderer();
    public function hasRenderer();

    public function getTemplate();
    public function hasTemplate();

    /**
     * @return ConfigInterface
     */
    public function getConfig();

    public function getSetting($name, $default = null);
}

