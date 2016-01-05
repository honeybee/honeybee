<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Textarea;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;
use Trellis\Runtime\Attribute\Textarea\TextareaAttribute;

class HtmlTextareaAttributeRenderer extends HtmlAttributeRenderer
{
    protected $removed_parameters = [ 'pattern' ];

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/textarea/as_itemlist_item_cell.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        // remove not supported options
        foreach ($this->removed_parameters as $param_key) {
            unset($params[$param_key]);
        }

        $params['maxlength'] = $this->getOption(
            'maxlength',
            $this->attribute->getOption(TextareaAttribute::OPTION_MAX_LENGTH)
        );
        $params['wrap'] = $this->getOption('wrap', '');
        $params['cols'] = $this->getOption('cols', '');
        $params['rows'] = $this->getOption('rows', 12);
        $params['syntax'] = $this->getSyntaxParameters();

        return $params;
    }

    protected function getSyntaxParameters()
    {
        $syntax_params = (array)$this->getOption('syntax', []);

        if (isset($syntax_params['enabled'])) {
            $syntax_params['name'] = isset($syntax_params['name']) ? $syntax_params['name'] : '';
            $syntax_params['preview'] = isset($syntax_params['preview']) ? $syntax_params['preview'] : '';
        }

        return $syntax_params;
    }
}
