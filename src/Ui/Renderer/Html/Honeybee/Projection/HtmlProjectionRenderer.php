<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee\Projection;

use Honeybee\Ui\Renderer\EntityRenderer;

class HtmlProjectionRenderer extends EntityRenderer
{
    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $resource = $this->getPayload('subject');

        $params['html_attributes'] = $this->getOption('html_attributes', []);

        $css = (string)$this->getOption('css', '');
        $css = 'hb-item ' . $this->getOption('css_prefix', 'hb-item-') . $resource->getIdentifier() . ' ' . $css;
        $css .= !empty($params['rendered_glance_content']) ? ' hb-item--has_glance' : '';
        $params['css'] = $css;

        return $params;
    }
}
