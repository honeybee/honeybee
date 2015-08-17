<?php

namespace Honeybee\Ui\Activity;

use Honeybee\Infrastructure\Config\SettingsInterface;
use Trellis\Common\Configurable;
use Exception;

class Activity extends Configurable implements ActivityInterface
{
    protected $name;
    protected $type = self::TYPE_GENERAL;
    protected $description = '';
    protected $label = '';
    protected $verb = '';
    protected $rels = [];
    protected $accepting = [];
    protected $sending = [];
    protected $settings = [];
    protected $url;

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getVerb()
    {
        return $this->verb;
    }

    public function getRels()
    {
        return $this->rels;
    }

    public function getAccepting()
    {
        return $this->accepting;
    }

    public function getSending()
    {
        return $this->sending;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getUrl()
    {
        return $this->url;
    }

    protected function setSettings(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    protected function setUrl(Url $url)
    {
        $this->url = $url;
    }
}
