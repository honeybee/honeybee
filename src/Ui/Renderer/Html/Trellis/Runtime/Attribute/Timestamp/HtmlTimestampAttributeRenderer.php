<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Timestamp;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;

class HtmlTimestampAttributeRenderer extends HtmlAttributeRenderer
{
    protected $removed_parameters = [ 'pattern' ];

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/datetime/as_input.twig';
    }

    protected function getWidgetImplementor()
    {
        return $this->getOption('widget_implementor', 'jsb_Honeybee_Core/ui/DatePicker');
    }
}
