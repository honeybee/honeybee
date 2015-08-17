<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Email;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;

class HtmlEmailAttributeRenderer extends HtmlAttributeRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/email/as_itemlist_item_cell.twig';
    }
}
