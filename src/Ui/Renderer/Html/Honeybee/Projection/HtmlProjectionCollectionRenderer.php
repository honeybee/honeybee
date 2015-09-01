<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee\Projection;

use Honeybee\Ui\Renderer\EntityListRenderer;

class HtmlProjectionCollectionRenderer extends EntityListRenderer
{
    const STATIC_TRANSLATION_PATH = "collection";

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['html_attributes'] = $this->getOption('html_attributes', []);

        $css = (string)$this->getOption('css', '');
        $css = 'hb-item-list ' . $css;
        $params['css'] = $css;

        return $params;
    }

    protected function getDefaultTranslationDomain()
    {
        $view_scope = $this->getOption('view_scope');

        if (empty($view_scope)) {
            $translation_domain_prefix = parent::getDefaultTranslationDomain();
        } else {
            // convention on view_scope value: the first 3 parts = vendor.package.resource_type
            $view_scope_parts = explode('.', $view_scope);
            $translation_domain_prefix = implode('.', array_slice($view_scope_parts, 0, 3));
        }

        $translation_domain = sprintf(
            '%s.%s',
            $translation_domain_prefix,
            self::STATIC_TRANSLATION_PATH
        );

        return $translation_domain;
    }
}
