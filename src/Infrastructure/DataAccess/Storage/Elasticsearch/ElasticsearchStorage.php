<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\DataAccess\Storage\Storage;

abstract class ElasticsearchStorage extends Storage
{
    protected function getIndex()
    {
        $fallback_index = $this->connector->getConfig()->get('index');

        return $this->config->get('index', $fallback_index);
    }

    protected function getType()
    {
        $fallback_type = $this->connector->getConfig()->get('type');

        return $this->config->get('type', $fallback_type);
    }

    protected function getParameters($method)
    {
        return (array)$this->config->get('parameters', new Settings)->get($method);
    }
}
