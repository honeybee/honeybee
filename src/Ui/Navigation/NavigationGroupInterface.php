<?php

namespace Honeybee\Ui\Navigation;

interface NavigationGroupInterface
{
    public function getName();

    public function getSettings();

    public function getNavigationItems();
}
