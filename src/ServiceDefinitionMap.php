<?php

namespace Honeybee;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Options;

class ServiceDefinitionMap extends TypedMap
{
    protected $options;

    public function __construct(Options $options = null)
    {
        parent::__construct([]);
        $this->options = $options;
    }

    public function getOption($option_key)
    {
        return $this->options->get($option_key);
    }

    public function hasOption($option_key)
    {
        return $this->options->has($option_key);
    }

    protected function getItemImplementor()
    {
        return ServiceDefinitionInterface::CLASS;
    }
}
