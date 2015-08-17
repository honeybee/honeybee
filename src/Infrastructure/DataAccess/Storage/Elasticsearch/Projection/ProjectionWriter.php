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

        $this->writeData($resource->getIdentifier(), $resource->toArray(), $settings);
    }
}
