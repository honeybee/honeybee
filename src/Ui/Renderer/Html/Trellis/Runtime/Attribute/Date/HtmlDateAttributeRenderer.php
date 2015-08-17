<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Date;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;

class HtmlDateAttributeRenderer extends HtmlAttributeRenderer
{
    const DEFAULT_VALUE_STEP = 0.001;

    protected $removed_parameters = [ 'pattern' ];

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/datetime/as_input.twig';
    }

    protected function getWidgetImplementor()
    {
        return 'jsb_Honeybee_Core/ui/DatePicker';
    }
}
