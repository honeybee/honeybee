<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Url;

use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;
use Trellis\Runtime\Attribute\Url\UrlAttribute;

class HtmlUrlAttributeRenderer extends HtmlAttributeRenderer
{
    const ALLOWED_SCHEMES_ECMA_PATTERN_TEMPLATE = '^(%s)(%s).*';
    const ALLOWED_SCHEMES_PLACEHOLDER_TEMPLATE = '(%s)%s';
    const ECMA_ESCAPABLE_CHARS = '[]|{}()*-+?.:,\\/^$#';

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/attribute/url/as_itemlist_item_cell.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $open_in_blank = $this->getOption('open_in_blank', true);
        $params['open_in_blank'] = $open_in_blank;

        return $params;
    }

    protected function getTranslations($translation_domain = null)
    {
        $translations = parent::getTranslations($translation_domain);

        // generate 'placeholder' and 'pattern' according to eventually defined settings
        if (!array_key_exists('pattern', $translations)
            && $this->attribute->hasOption(UrlAttribute::OPTION_ALLOWED_SCHEMES)) {
            $allowed_schemes = $this->attribute->getOption(UrlAttribute::OPTION_ALLOWED_SCHEMES);
            list($pattern, $placeholder) = $this->generateEcmaPatternBySchemes($allowed_schemes);

            $translations['pattern'] = $pattern;

            if (!array_key_exists('placeholder', $translations)) {
                $translations['placeholder'] = $placeholder;
            }
        }

        return $translations;
    }

    /**
     * Generate a case-insensitive ECMA regurlar expression,
     * and its corresponding placeholder, for the allowed schemes
     */
    protected function generateEcmaPatternBySchemes(array $schemes)
    {
        if (empty($schemes)) {
            return ['', ''];
        }
        $case_insensitive_schemes_patterns = [];
        $escaped_separator = '';

        // case-insensitive schemes
        foreach ($schemes as $schema) {
            $case_insensitive_schemes_patterns[] = join(
                array_map(
                    function ($char) {
                        return sprintf(
                            '[%s%s]',
                            strtoupper($char),
                            strtolower($char)
                        );
                    },
                    str_split($schema)
                )
            );
        }
        // escape scheme separator
        $scheme_separator = $this->attribute->getOption(UrlAttribute::OPTION_SCHEME_SEPARATOR, '://');
        foreach (str_split($scheme_separator) as $char) {
            $escaped_separator .= (false !== strpos(self::ECMA_ESCAPABLE_CHARS, $char) ? '\\'.$char : $char);
        }

        return [
            // Allowed schemes regex pattern
            sprintf(
                self::ALLOWED_SCHEMES_ECMA_PATTERN_TEMPLATE,
                implode('|', $case_insensitive_schemes_patterns),
                $escaped_separator
            ),
            // Allowed schemes placeholder
            sprintf(
                self::ALLOWED_SCHEMES_PLACEHOLDER_TEMPLATE,
                implode(' | ', $schemes),
                $scheme_separator
            )
        ];
    }
}
