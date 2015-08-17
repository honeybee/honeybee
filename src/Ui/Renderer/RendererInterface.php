<?php

namespace Honeybee\Ui\Renderer;

interface RendererInterface
{
    public function render($payload, $settings = null);
}
