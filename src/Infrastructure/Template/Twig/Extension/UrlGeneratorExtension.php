<?php

namespace Honeybee\Infrastructure\Template\Twig\Extension;

use Honeybee\Ui\UrlGeneratorInterface;
use Twig_Extension;
use Twig_Function_Method;

/**
 * Extension that wraps the UrlGeneratorInterface methods to make them available in twig templates.
 */
class UrlGeneratorExtension extends Twig_Extension
{
    protected $url_generator;

    public function __construct(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    public function getFunctions()
    {
        return [
            'generateUrl' => new Twig_Function_Method($this, 'generateUrl'),
        ];
    }

    /**
     * Generates a URL by using the given route name, uri template or activity.
     *
     * @param string $name route name, uri template or activity to generate an URL for
     * @param array $parameters pairs of placeholder names and values
     * @param array $options array of options to influence URL generation
     *
     * @return string relative or absolute URL
     */
    public function generateUrl($name, array $parameters = [], array $options = [])
    {
        return $this->url_generator->generateUrl($name, $parameters, $options);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string extension name.
     */
    public function getName()
    {
        return static::CLASS;
    }
}
