<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee;

use Honeybee\Ui\Renderer\EntityRenderer;
use Honeybee\Infrastructure\Config\ArrayConfig;

class HtmlEntityRenderer extends EntityRenderer
{
    const GLANCE_RENDERER_LOCATOR_MODIFIER = 'Glance';

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $glance_config = $this->getOption('glance_config', new ArrayConfig([]));

        $params['html_attributes'] = $this->getOption('html_attributes', []);
        $params['trigger_id'] = $this->getOption('trigger_id', sprintf('%s-%s', $params['grouped_base_path'], rand()));
        $params['expand_content_disabled'] = $this->getOption('expand_content_disabled', false);
        $params['expand_content_by_default'] = $this->getOption('expand_content_by_default', false);
        $params['rendered_glance_content'] = $glance_config->get('enabled', false)
            ? $this->renderGlance($glance_config->toArray())
            : '';

        // expand if no clickable glance is rendered
        if (empty($params['rendered_glance_content'])) {
            $params['expand_content_by_default'] = true;
        }

        return $params;
    }

    protected function renderGlance($renderer_config = [])
    {
        $view_scope = $this->getOption('view_scope', 'missing_view_scope');
        $resource = $this->getPayload('subject');
        $output_format = $this->output_format;

        $renderer_config_default = [
            'view_scope' => $view_scope,
            // render with {subject}GlanceRenderer
            'renderer_locator_modifier' => self::GLANCE_RENDERER_LOCATOR_MODIFIER
        ];

        $renderer_config = new ArrayConfig(
            array_replace_recursive(
                $renderer_config_default,
                $renderer_config
            )
        );

        return $this->renderer_service->renderSubject($resource, $this->output_format, $renderer_config);
    }
}
