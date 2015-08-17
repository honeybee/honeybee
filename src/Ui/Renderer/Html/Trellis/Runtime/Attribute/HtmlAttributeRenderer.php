<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute;

use Honeybee\Ui\Renderer\AttributeRenderer;
use Honeybee\Projection\ProjectionInterface;

class HtmlAttributeRenderer extends AttributeRenderer
{
    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['html_attributes'] = $this->getOption('html_attributes', []);
        // get common parameters for input HTML elements
        $params = array_replace_recursive($params, $this->getInputTemplateParameters());
        $params['pattern'] = array_key_exists('pattern', $params['translations'])
            ? sprintf('pattern="%s"', $params['translations']['pattern'])
            : '';

        $css = sprintf(
            'hb-attribute %s%s %s',
            $this->getOption('css_prefix', 'attribute_value_'),
            $params['attribute_name'],
            (string)$this->getOption('field_css', '')
        );
        if ($this->isWidgetEnabled()) {
            $css .= sprintf(' %s %s', $params['is_embedded'] ? ' jsb__' : ' jsb_', $this->getWidgetImplementor());
        }

        $params['css'] = $css;
        $params['widget_enabled'] = $this->isWidgetEnabled();
        $params['widget_options'] = $this->getWidgetOptions();

        return $params;
    }

    protected function getInputTemplateParameters()
    {
        $global_input_parameters['disabled'] = $this->isDisabled() ? 'disabled' : '';
        $global_input_parameters['readonly'] = $this->isReadonly() ? 'readonly' : '';
        /*
            When rendering attributes of an Entity embedded in a Resource/AggregateRoot, adding the HTML 'required'
            property would prevent the submitting of the EmbeddedEntityList HTML attribute (where the Entity of the
            current attribute is cointained) even when the latter one is not mandatory.
            The 'required' status will be stored as HTML 'data-' property (and eventually restored through JS Widget),
            so that the common 'required' property will not block the form submitting, when the parent embedded Entity
            is not intended to be added.
        */
        if ($this->isRequired()) {
            if ($this->getPayload('resource') instanceof ProjectionInterface) {
                $global_input_parameters['required'] = 'required';
            } else {
                $global_input_parameters['required'] = 'data-required';
            }
        } else {
            $global_input_parameters['required'] = '';
        }

        return $global_input_parameters;
    }

    protected function isWidgetEnabled()
    {
        return (bool)$this->getOption('widget_enabled', $this->getWidgetImplementor() !== null);
    }

    protected function getWidgetOptions()
    {
        $widget_options = [
            'isReadonly' => $this->isReadonly(),
            'isDisabled' => $this->isDisabled(),
            'isRequired' => $this->isRequired()
        ];

        return array_replace_recursive($widget_options, (array)$this->getOption('widget_options', []));
    }

    protected function getDefaultTranslationKeys()
    {
        return array_merge(parent::getDefaultTranslationKeys(), [ 'placeholder', 'pattern' ]);
    }

    protected function isDisabled()
    {
        return (bool)($this->getOption('disabled', false));
    }

    protected function getWidgetImplementor()
    {
        return null;
    }
}
