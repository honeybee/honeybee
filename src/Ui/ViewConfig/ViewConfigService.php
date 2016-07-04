<?php

namespace Honeybee\Ui\ViewConfig;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;
use Trellis\Common\Object;

class ViewConfigService extends Object implements ViewConfigServiceInterface
{
    protected $config;

    protected $view_config;

    protected $name_resolver;

    protected $view_config_map;

    public function __construct(
        ConfigInterface $config,
        ConfigInterface $view_config,
        NameResolverInterface $name_resolver
    ) {
        $this->config = $config;
        $this->view_config = $view_config;
        $this->view_config_map = new ViewConfigMap();
        $this->name_resolver = $name_resolver;
    }

    /**
     * Returns the ViewConfig instance for the given scope name.
     *
     * @return ViewConfigInterface
     */
    public function getViewConfig($scope = null)
    {
        if (!$this->view_config_map->hasKey($scope)) {
            $this->buildViewConfig($scope);
        }

        return $this->view_config_map->getItem($scope);
    }

    /**
     * @return array all scopes of views from the config
     */
    public function getViewConfigScopes()
    {
        return $this->view_config->getKeys();
    }

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
    ) {
        $view_config = $this->getViewConfig($view_scope);

        $renderer_config_name = $this->name_resolver->resolve($subject_or_name);

        $renderer_config = $view_config->getRendererConfig($renderer_config_name, $output_format, $default_data);

        return $renderer_config;
    }

    protected function buildViewConfig($scope)
    {
        if (is_null($scope)
            || (!$this->view_config->has($scope) && false === $this->config->get('create_missing', false))
        ) {
            throw new RuntimeError(
                sprintf(
                    'View config for the given scope "%s" has not been configured. Please set a "view_scope" ' .
                    'attribute in your action: $this->setAttribute("view_scope", $this->getScopeKey())',
                    $scope
                )
            );
        }

        $view_config = $this->view_config->get($scope, new ViewConfig([]));

        $this->view_config_map->setItem($scope, new ViewConfig($view_config->toArray()));
    }
}
