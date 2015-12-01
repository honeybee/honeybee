<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\ElasticsearchStorageWriter;
use Honeybee\Projection\ProjectionInterface;

class ProjectionWriter extends ElasticsearchStorageWriter
{
    public function write($resource, SettingsInterface $settings = null)
    {
        if (!$resource instanceof ProjectionInterface) {
            throw new RuntimeError(
                sprintf('Invalid payload given to %s, expected type of %s', __METHOD__, ProjectionInterface::CLASS)
            );
        }

$time = microtime(true);
        $this->writeData($resource->getIdentifier(), $resource->toArray(), $settings);
$now = microtime(true);
error_log('Elasticsearch ProjectionWriter::write w/ toArray ' . $resource->getIdentifier() . ': ' . round(($now - $time) * 1000, 1) . 'ms');
    }
}
