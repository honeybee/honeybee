<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Infrastructure\Config\ConfigInterface;

interface RendererLocatorInterface
{
    /**
     * Tries to find renderer implementor given via config 'renderer' key and
     * then tries to find a renderer based on the type/class of the subject.
     *
     * @param mixed $subject subject to render
     * @param ConfigInterface $renderer_config configuration for the renderer when one is found
     *
     * @return string renderer implementor for the given subject (including namespace)
     */
    public function locateRendererFor($subject, ConfigInterface $renderer_config = null);

    /**
     * @return OutputFormatInterface output format the locator is valid for
     */
    public function getOutputFormat();
}
