<?php

namespace Honeybee\Ui\Activity;

use Trellis\Common\Object;

class Url extends Object
{
    const TYPE_URI = 'uri';

    const TYPE_URI_TEMPLATE = 'uri_template';

    const TYPE_ROUTE = 'route';

    protected $type = self::TYPE_URI;

    protected $value;

    protected $parameters = [];

    public function getType()
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function hasParameter($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    public function getParameter($key, $default = null)
    {
        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * @param string $route_name name of the framework route
     * @param array $parameters parameters for the url generation
     *
     * @return Url of type route
     */
    public static function createRoute($route_name, array $parameters = [])
    {
        return new static([
            'value' => $route_name,
            'type' => self::TYPE_ROUTE,
            'parameters' => $parameters
        ]);
    }

    /**
     * @param string $uri_template URL template
     * @param array $parameters parameters for the template expansion
     *
     * @return Url of type uri_template
     */
    public static function createUriTemplate($uri_template, array $parameters = [])
    {
        return new static([
            'value' => $uri_template,
            'type' => self::TYPE_URI_TEMPLATE,
            'parameters' => $parameters
        ]);
    }

    /**
     * @param string $uri URL
     * @param array $parameters parameters (may e.g. be used as query parameters to the given uri)
     *
     * @return Url of type uri
     */
    public static function createUri($uri, array $parameters = [])
    {
        return new static([
            'value' => $uri,
            'type' => self::TYPE_URI,
            'parameters' => $parameters
        ]);
    }

    public function __toString()
    {
        return (string)$this->value;
    }
}
