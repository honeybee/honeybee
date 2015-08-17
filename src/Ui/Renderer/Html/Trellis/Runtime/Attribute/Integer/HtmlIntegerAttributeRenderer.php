<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Integer;

use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;

class HtmlIntegerAttributeRenderer extends HtmlAttributeRenderer
{
    protected $removed_parameters = [ 'pattern' ];

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/integer/as_itemlist_item_cell.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        // remove not supported options
        foreach ($this->removed_parameters as $param_key) {
            unset($params[$param_key]);
        }

        // load settings or fallback to default attribute's options
        $params['min_value'] = $this->getOption('min_value', $this->attribute->getOption(IntegerAttribute::OPTION_MIN_VALUE));
        $params['max_value'] = $this->getOption('max_value', $this->attribute->getOption(IntegerAttribute::OPTION_MAX_VALUE));
        $params['value_step'] = $this->getOption('value_step');

        // verify the parameters are valid with integers
        $php_int_min = ~PHP_INT_MAX;

        foreach ([ 'min_value', 'max_value', 'value_step'] as $key) {
            if (is_numeric($params[$key]) && $params[$key] <= PHP_INT_MAX && $params[$key] >= $php_int_min) {
                // truncate to integer eventual float values
                $params[$key] = intval(floor($params[$key]));
            } else {
                $params[$key] = '';
            }
        }

        if (!$this->hasOption('placeholder')) {
            $params['placeholder'] = sprintf(
                '%s…%s',
                $params['min_value'],
                $params['max_value']
            );
        }

        return $params;
    }
}
