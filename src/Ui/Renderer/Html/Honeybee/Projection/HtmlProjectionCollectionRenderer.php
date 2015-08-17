<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee\Projection;

use Honeybee\Ui\Renderer\EntityListRenderer;

class HtmlProjectionCollectionRenderer extends EntityListRenderer
{
    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['html_attributes'] = $this->getOption('html_attributes', []);

        $css = (string)$this->getOption('css', '');
        $css = 'hb-item-list ' . $css;
        $params['css'] = $css;

        return $params;
    }
}
