<?php

namespace Honeybee\Infrastructure\DataAccess;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\DataAccess\Finder\FinderMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderMap;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\DataAccess\UnitOfWork\UnitOfWorkMap;
use Honeybee\Projection\ProjectionTypeInterface;

class DataAccessService implements DataAccessServiceInterface
{
    protected $storage_writer_map;

    protected $storage_reader_map;

    protected $finder_map;

    protected $query_service_map;

    protected $uow_map;

    public function __construct(
        StorageWriterMap $storage_writer_map,
        StorageReaderMap $storage_reader_map,
        FinderMap $finder_map,
        QueryServiceMap $query_service_map,
        UnitOfWorkMap $uow_map
    ) {
        $this->storage_writer_map = $storage_writer_map;
        $this->storage_reader_map = $storage_reader_map;
        $this->finder_map = $finder_map;
        $this->query_service_map = $query_service_map;
        $this->uow_map = $uow_map;
    }

    public function getStorageWriterMap()
    {
        return $this->storage_writer_map;
    }

    public function getStorageWriter($writer_name)
    {
        if (!$this->storage_writer_map->hasKey($writer_name)) {
            throw new RuntimeError(sprintf('Storage-writer %s has not been configured.', $writer_name));
        }
        return $this->storage_writer_map->getItem($writer_name);
    }

    public function getProjectionWriterByType(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'writer');
    }

    public function getStorageReaderMap()
    {
        return $this->storage_reader_map;
    }

    public function getStorageReader($reader_name)
    {
        if (!$this->storage_reader_map->hasKey($reader_name)) {
            throw new RuntimeError(sprintf('Storage-reader %s has not been configured.', $reader_name));
        }
        return $this->storage_reader_map->getItem($reader_name);
    }

    public function getProjectionReaderByType(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'reader');
    }

    public function getFinderMap()
    {
        return $this->finder_map;
    }

    public function getFinder($finder_name)
    {
        if (!$this->finder_map->hasKey($finder_name)) {
            throw new RuntimeError(sprintf('Finder %s has not been configured.', $finder_name));
        }
        return $this->finder_map->getItem($finder_name);
    }

    public function getProjectionFinderByType(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'finder');
    }

    public function getQueryServiceMap()
    {
        return $this->query_service_map;
    }

    public function getQueryService($query_service_name)
    {
        if (!$this->query_service_map->hasKey($query_service_name)) {
            throw new RuntimeError(sprintf('QueryService "%s" has not been configured.', $query_service_name));
        }
        return $this->query_service_map->getItem($query_service_name);
    }

    public function getProjectionQueryServiceByType(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'query_service');
    }

    public function getUnitOfWorkMap()
    {
        return $this->uow_map;
    }

    public function getUnitOfWork($uow_name)
    {
        if (!$this->uow_map->hasKey($uow_name)) {
            throw new RuntimeError(sprintf('UnitOfWork %s has not been configured.', $uow_name));
        }
        return $this->uow_map->getItem($uow_name);
    }

    public function writeTo($writer_name, $payload, array $settings = [])
    {
        $storage_writer = $this->getStorageWriter($writer_name);
        $storage_writer->write($payload, new Settings($settings));
    }

    public function readFrom($reader_name, $identifier, array $settings = [])
    {
        $storage_reader = $this->getStorageReader($reader_name);
        return $storage_reader->read($identifier, new Settings($settings));
    }

    public function deleteFrom($writer_name, $identifier, array $settings = [])
    {
        $storage_writer = $this->getStorageWriter($writer_name);
        $storage_writer->delete($identifier, new Settings($settings));
    }

    protected function getDataAccessComponent(ProjectionTypeInterface $projection_type, $component)
    {
        $component_key = sprintf('%s::view_store::%s', $projection_type->getVariantPrefix(), $component);

        switch ($component) {
            case 'finder':
                return $this->getFinder($component_key);
                break;
            case 'reader':
                return $this->getStorageReader($component_key);
                break;
            case 'writer':
                return $this->getStorageWriter($component_key);
                break;
            case 'query_service':
                return $this->getQueryService($component_key);
                break;
        }

        throw new RuntimeError(
            sprintf(
                'Invalid DBAL component: %s. Supported components are: ' .
                '"finder", "reader", "writer" and "query_service".',
                $component
            )
        );
    }
}
