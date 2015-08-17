<?php

namespace Honeybee\Infrastructure\Template;

interface TemplateRendererInterface
{
    /**
     * Renders the given template using the given variables and returns the
     * rendered result.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process
     *
     * @return mixed result of the rendered template
     */
    public function render($template, array $data = [], array $settings = []);

    /**
     * Renders the wanted template using the given variables as context into
     * the specified target file.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param string $target_file local filesystem target path location (including file name)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process
     */
    public function renderToFile($template, $target_location, array $data = [], array $settings = []);

    /**
     * Renders the given template source code string using the given data and
     * returns the rendered string.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process
     *
     * @return string result of the rendered template source
     */
    public function renderToString($template, array $data = [], array $settings = []);
}
