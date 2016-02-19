<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee;

use Honeybee\Ui\Renderer\EntityRenderer;
use Honeybee\Infrastructure\Config\ArrayConfig;

class HtmlEntityRenderer extends EntityRenderer
{
    const GLANCE_CONFIG_GLOBAL_SCOPE = 'application';
    const GLANCE_RENDERER_CONFIG_NAME = '#glance';
    const GLANCE_RENDERER_LOCATOR_MODIFIER = 'Glance';

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['html_attributes'] = $this->getOption('html_attributes', []);
        $params['trigger_id'] = $this->getOption('trigger_id', sprintf('%s-%s', $params['grouped_base_path'], rand()));
        $params['expand_content_disabled'] = $this->getOption('expand_content_disabled', false);
        $params['expand_content_by_default'] = $this->getOption('expand_content_by_default', false);
        $params['rendered_glance_content'] = $this->getOption('glance_enabled', false) ? $this->renderGlance() : '';

        // expand if no clickable glance is rendered
        if (empty($params['rendered_glance_content'])) {
            $params['expand_content_by_default'] = true;
        }

        return $params;
    }

    protected function renderGlance()
    {
        $view_scope = $this->getOption('view_scope', 'missing_view_scope');
        $resource = $this->getPayload('subject');
        $output_format = $this->output_format;

        $renderer_config_default = [
            'view_scope' => $view_scope,
            'renderer_locator_modifier' => self::GLANCE_RENDERER_LOCATOR_MODIFIER   // render with {subject}GlanceRenderer
        ];
        // support glance options also directly on the entity
        $renderer_config_default = array_replace_recursive($renderer_config_default, (array)$this->getOption('glance_config', []));

        // view_config for generic glance scope.
        $renderer_config_global = $this->view_config_service->getRendererConfig(
            self::GLANCE_CONFIG_GLOBAL_SCOPE,
            $this->output_format,
            self::GLANCE_RENDERER_CONFIG_NAME
        );

        // view_config for whole view.
        $renderer_config_view = $this->view_config_service->getRendererConfig(
            $view_scope,
            $this->output_format,
            self::GLANCE_RENDERER_CONFIG_NAME
        );

        // check view_config for resource type.
        $resource_type_renderer_config_name = sprintf(
            '%s.%s',
            $resource->getType()->getScopeKey(),
            self::GLANCE_RENDERER_CONFIG_NAME
        );
        $renderer_config = $this->view_config_service->getRendererConfig(
            $view_scope,
            $this->output_format,
            $resource_type_renderer_config_name
        );

        // fallback sequence
        $renderer_config = new ArrayConfig(
            array_replace_recursive(
                $renderer_config_default,
                $renderer_config_global->toArray(),
                $renderer_config_view->toArray(),
                $renderer_config->toArray()
            )
        );

        if (!$renderer_config->get('enabled', false)) {
            return '';
        }

        return $this->renderer_service->renderSubject($resource, $this->output_format, $renderer_config);
    }
}
