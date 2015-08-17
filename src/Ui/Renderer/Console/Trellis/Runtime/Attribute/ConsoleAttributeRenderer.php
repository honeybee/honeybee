<?php

namespace Honeybee\Ui\Renderer\Console\Trellis\Runtime\Attribute;

use Honeybee\Ui\Renderer\AttributeRenderer;

class ConsoleAttributeRenderer extends AttributeRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/as_list_item.twig';
    }

    protected function getTemplateParameters()
    {
        return parent::getTemplateParameters();
    }
}
