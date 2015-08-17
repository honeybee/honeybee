<?php

namespace Honeybee\Ui\Renderer;

class GenericLink
{
    protected $uri;
    protected $rels = [];
    protected $attributes = [];

    public function __construct($uri, $rels, array $attributes = [])
    {
        $this->uri = $uri;

        if (is_array($rels)) {
            $this->rels = $rels;
        } else {
            $this->rels = [ (string)$rels ];
        }

        $this->attributes = $attributes;
    }

    public function setRels($rels)
    {
        if (is_array($rels)) {
            $this->rels = $rels;
        } else {
            $this->rels = [ (string)$rels ];
        }
    }

    public function getRels()
    {
        return $this->rels;
    }

    public function getFirstRel()
    {
        return isset($this->rels[0]) ? $this->rels[0] : '';
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default_value = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        } else {
            return $default_value;
        }
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }
}
