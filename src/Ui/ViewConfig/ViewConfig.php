<?php

namespace Honeybee\Ui\ViewConfig;

use Trellis\Common\Object;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;

class ViewConfig extends Object implements ViewConfigInterface
{
    protected $scope = 'default';
    protected $activities;
    protected $settings;
    protected $slots;
    protected $output_formats;

    public function __construct(array $state = [])
    {
        $this->activities = new Settings([]);
        $this->settings = new Settings([]);
        $this->slots = new Settings([]);
        $this->output_formats = new Settings([]);

        parent::__construct($state);
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getActivities()
    {
        return $this->activities;
    }

    protected function setActivities($activities)
    {
        if (is_array($activities)) {
            $activities = new Settings($activities);
        }

        $this->activities = $activities;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    protected function setSettings($settings)
    {
        if (is_array($settings)) {
            $settings = new Settings($settings);
        }

        $this->settings = $settings;
    }

    public function getSlots()
    {
        return $this->slots;
    }

    protected function setSlots($slots)
    {
        if (is_array($slots)) {
            $slots = new Settings($slots);
        }

        $this->slots = $slots;
    }

    public function getOutputFormats()
    {
        return $this->output_formats;
    }

    protected function setOutputFormats($output_formats)
    {
        if (is_array($output_formats)) {
            $output_formats = new Settings($output_formats);
        }

        $this->output_formats = $output_formats;
    }

    /**
     * Returns the config of that name for the given output format.
     *
     * @param string $subject_name name of the renderer_config part
     * @param OutputFormatInterface $output_format output format the renderer name is part of
     * @param array $default_data initial values to use as renderer config; config will be merged over this
     *
     * @return ArrayConfig
     */
    public function getRendererConfig($subject_name, OutputFormatInterface $output_format, array $default_data = [])
    {
        if (!$this->output_formats || !$this->output_formats->has($output_format->getName())) {
            return new ArrayConfig($default_data);
        }

        $output_format_info = $this->output_formats->get($output_format->getName());

        if ($output_format_info && $output_format_info->has($subject_name)) {
            $settings = $output_format_info->get($subject_name);
            return new ArrayConfig(array_merge($default_data, $settings->toArray()));
        }

        return new ArrayConfig($default_data);
    }
}
