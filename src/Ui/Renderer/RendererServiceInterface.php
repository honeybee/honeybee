<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;

interface RendererServiceInterface
{
    public function renderSubject(
        $subject,
        OutputFormatInterface $output_format,
        ConfigInterface $renderer_config = null,
        array $additional_payload = [],
        SettingsInterface $render_settings = null
    );

    public function getRenderer($subject, OutputFormatInterface $output_format, ConfigInterface $config = null);
}
