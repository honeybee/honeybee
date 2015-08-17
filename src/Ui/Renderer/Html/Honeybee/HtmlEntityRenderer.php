<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee;

use Honeybee\Ui\Renderer\EntityRenderer;

class HtmlEntityRenderer extends EntityRenderer
{
    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['html_attributes'] = $this->getOption('html_attributes', []);

        return $params;
    }
}
