<?php

namespace Honeybee\Ui\ViewConfig;

use Honeybee\Ui\OutputFormat\OutputFormatInterface;

interface ViewConfigInterface
{
    public function getScope();
    public function getActivities();
    public function getSettings();
    public function getSlots();
    public function getOutputFormats();

    /**
     * Returns the config of that name for the given output format.
     *
     * @param string $subject_name name of the renderer_config part
     * @param OutputFormatInterface $output_format output format the renderer name is part of
     * @param array $default_data initial values to use as renderer config; config will be merged over this
     *
     * @return ArrayConfig
     */
    public function getRendererConfig($subject_name, OutputFormatInterface $output_format, array $default_data = []);
}
