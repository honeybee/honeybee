<?php

namespace Honeybee\Infrastructure\Template\Twig\Extension;

use Honeybee\Common\Util\StringToolkit;
use Twig_Extension;
use Twig_Filter_Method;
use Twig_Function_Method;

/**
 * Extension that adds some filters that may be useful.
 */
class ToolkitExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            'cast_to_array' => new Twig_Filter_Method($this, 'castToArray'),
            'as_studly_caps' => new Twig_Filter_Method($this, 'asStudlyCaps'),
            'as_camel_case' => new Twig_Filter_Method($this, 'asCamelCase'),
            'as_snake_case' => new Twig_Filter_Method($this, 'asSnakeCase'),
            'format_bytes' => new Twig_Filter_Method($this, 'formatBytes')
        );
    }

    public function getFunctions()
    {
        return array(
            'starts_with' => new Twig_Function_Method($this, 'startsWith'),
            'ends_with' => new Twig_Function_Method($this, 'endsWith'),
            'replace' => new Twig_Function_Method($this, 'replace'),
        );
    }

    public function castToArray($value)
    {
        return (array)$value;
    }

    public function asStudlyCaps($value)
    {
        return StringToolkit::asStudlyCaps($value);
    }

    public function asCamelCase($value)
    {
        return StringToolkit::asCamelCase($value);
    }

    public function asSnakeCase($value)
    {
        return StringToolkit::asSnakeCase($value);
    }

    public function formatBytes($value)
    {
        return StringToolkit::formatBytes($value);
    }

    public function startsWith($haystack, $needle)
    {
        return StringToolkit::startsWith($haystack, $needle);
    }

    public function endsWith($haystack, $needle)
    {
        return StringToolkit::endsWith($haystack, $needle);
    }


    public function replace($subject, $search, $replace, $count = null)
    {
        if (is_null($count)) {
            return str_replace($search, $replace, $subject);
        }

        return str_replace($search, $replace, $subject, $count);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string extension name.
     */
    public function getName()
    {
        return ToolkitExtension::CLASS;
    }
}
