<?php

namespace Honeybee;

interface EnvironmentInterface
{
    /**
     * @return \Honeybee\UserInterface
     */
    public function getUser();

    /**
     * @return \Honeybee\Infrastructure\Config\SettingsInterface
     */
    public function getSettings();
}
