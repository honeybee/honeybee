<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Timestamp;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;
use Honeybee\Common\Util\StringToolkit;

class HtmlTimestampAttributeRenderer extends HtmlAttributeRenderer
{
    protected $removed_parameters = [ 'pattern' ];

    protected function getDefaultTemplateIdentifier()
    {
        $view_scope = $this->getOption('view_scope', 'missing_view_scope.collection');
        if (StringToolkit::endsWith($view_scope, 'collection')) {
            return $this->output_format->getName() . '/attribute/datetime/as_itemlist_item_cell.twig';
        }

        return $this->output_format->getName() . '/attribute/datetime/as_input.twig';
    }

    protected function getWidgetImplementor()
    {
        return $this->getOption('widget_implementor', 'jsb_Honeybee_Core/ui/DatePicker');
    }
}
