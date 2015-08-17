<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\TextList;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;

class HtmlTextListAttributeRenderer extends HtmlAttributeRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/text-list/as_itemlist_item_cell.twig';
    }

    protected function determineAttributeValue($attribute_name, $default_value = '')
    {
        $value = [];

        if ($this->hasOption('value')) {
            $value = $this->getOption('value', $default_value);
            $value = is_array($value) ? $value : [ $value ];
            return $value;
        }

        $expression = $this->getOption('expression');
        if (!empty($expression)) {
            $value = $this->evaluateExpression($expression);
        } else {
            $value = $this->getPayload('resource')->getValue($attribute_name);
        }

        $value = is_array($value) ? $value : [ $value ];

        return $value;
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['grouped_field_name'] = $params['grouped_field_name'] . '[]';

        return $params;
    }

    protected function getWidgetOptions()
    {
        $widget_options = parent::getWidgetOptions();

        $widget_options['min_count'] = $this->getMinCount($this->isRequired());
        $widget_options['max_count'] = $this->getMaxCount();

        return $widget_options;
    }

    protected function getMinCount($is_required = false)
    {
        $min_count = $this->getOption('min_count');

        if (!is_numeric($min_count) && $is_required) {
            $min_count = 1;
        }
        return $min_count;
    }

    protected function getMaxCount()
    {
        return $this->getOption('max_count');
    }

    protected function isRequired()
    {
        $is_required = parent::isRequired();

        $text_list = $this->determineAttributeValue($this->attribute->getName());

        // check options against actual value
        $items_number = count($text_list);
        $min_count = $this->getMinCount($is_required);

        if (is_numeric($min_count) && $items_number < $min_count) {
            $is_required = true;
        }

        return $is_required;
    }

    protected function getWidgetImplementor()
    {
        return 'jsb_Honeybee_Core/ui/TextList';
    }
}
