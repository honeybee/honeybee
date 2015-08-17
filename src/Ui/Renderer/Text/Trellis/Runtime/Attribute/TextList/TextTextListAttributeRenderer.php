<?php

namespace Honeybee\Ui\Renderer\Text\Trellis\Runtime\Attribute\TextList;

use Honeybee\Ui\Renderer\Text\Trellis\Runtime\Attribute\TextAttributeRenderer;

class TextTextListAttributeRenderer extends TextAttributeRenderer
{
    protected function determineAttributeValue($attribute_name, $default_value = '')
    {
        $value = [];

        if ($this->hasOption('value')) {
            $value = $this->getOption('value', $default_value);
            $value = is_array($value) ? $value : [ $value ];
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
}
