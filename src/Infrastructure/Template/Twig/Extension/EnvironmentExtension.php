<?php

namespace Honeybee\Infrastructure\Template\Twig\Extension;

use Honeybee\EnvironmentInterface;
use Twig_Extension;
use Twig_Function_Method;

/**
 * Extension that wraps the EnvironmentInterface methods to make them available in twig templates.
 */
class EnvironmentExtension extends Twig_Extension
{
    protected $environment;

    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return [
            'getEnvironment' => new Twig_Function_Method($this, 'getEnvironment'),
        ];
    }

    /**
     * @return EnvironmentInterface current environment
     */
    public function getEnvironment()
    {
        return $this->environment;
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
