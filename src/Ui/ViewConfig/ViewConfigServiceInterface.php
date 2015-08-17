<?php

namespace Honeybee\Ui\ViewConfig;

use Honeybee\Ui\OutputFormat\OutputFormatInterface;

interface ViewConfigServiceInterface
{
    /**
     * Returns the ViewConfig instance for the given scope name.
     *
     * @return ViewConfigInterface
     */
    public function getViewConfig($scope = null);

    /**
     * @return array all scopes of views from the config
     */
    public function getViewConfigScopes();

    /**
     * Returns the renderer configuration for the given view scope, output format and subject.
     *
     * @param string $view_scope name of the view scope to get the renderer config from
     * @param OutputFormatInterface $output_format output format to get the renderer config from
     * @param mixed $subject_or_name subject (object) or actual renderer_config name string to use for lookup
     * @param array $default_data config data to merge into the renderer_config that is returned
     *
     * @return ArrayConfig renderer configuration
     */
    public function getRendererConfig(
        $view_scope,
        OutputFormatInterface $output_format,
        $subject_or_name,
        array $default_data = []
    );
}
