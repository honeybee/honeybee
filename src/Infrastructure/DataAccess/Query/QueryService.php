<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Psr\Log\LoggerInterface;

abstract class QueryService implements QueryServiceInterface
{
    protected $config;

    protected $finder_mappings;

    protected $logger;

    public function __construct(
        ConfigInterface $config,
        array $finder_mappings,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->finder_mappings = $finder_mappings;
        $this->logger = $logger;
    }

    protected function getFinder($mapping_name = null)
    {
        $mapping_name = $mapping_name ?: $this->config->get('default_mapping', 'default');

        Assertion::string($mapping_name);

        if (!isset($this->finder_mappings[$mapping_name])) {
            throw new RuntimeError('No finder mapping configured for key: ' . $mapping_name);
        }
        return $this->finder_mappings[$mapping_name]['finder'];
    }

    protected function getQueryTranslation($mapping_name = null)
    {
        $mapping_name = $mapping_name ?: $this->config->get('default_mapping', 'default');

        Assertion::string($mapping_name);

        if (!isset($this->finder_mappings[$mapping_name])) {
            throw new RuntimeError('No finder mapping configured for key: ' . $mapping_name);
        }
        return $this->finder_mappings[$mapping_name]['query_translation'];
    }

    public function __call($method, array $args)
    {
        $callable = [ $this->getFinder(), $method ];
        if (is_callable($callable)) {
            return call_user_func_array($callable, $args);
        }
        throw new RuntimeError('Call to undefined method ' . $method . ' on ' . static::CLASS);
    }
}
