<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\GeoPoint;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;
use Trellis\Runtime\Attribute\GeoPoint\GeoPointAttribute;

class HtmlGeoPointAttributeRenderer extends HtmlAttributeRenderer
{
    const DEFAULT_VALUE_STEP = 'any';

    protected $removed_parameters = [ 'pattern', 'placeholder' ];

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/geo-point/as_itemlist_item_cell.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        // remove not supported options
        foreach ($this->removed_parameters as $param_key) {
            unset($params[$param_key]);
        }

        $params['value_step'] = $this->getOption('value_step', self::DEFAULT_VALUE_STEP);

        // verify the parameters are valid with floats
        foreach ([ 'value_step' ] as $key) {
            if ($key === 'value_step' && $params[$key] === 'any') {
                continue;
            }
            if (is_numeric($params[$key])) {
                $params[$key] = floatval($params[$key]);
            } else {
                $params[$key] = '';
            }
        }

        return $params;
    }
}
