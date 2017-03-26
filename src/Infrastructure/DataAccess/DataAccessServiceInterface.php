<?php

namespace Honeybee\Infrastructure\DataAccess;

use Honeybee\Projection\ProjectionTypeInterface;

interface DataAccessServiceInterface
{
    public function getStorageWriterMap();

    public function getStorageWriter($writer_name);

    public function getProjectionWriterByType(ProjectionTypeInterface $projection_type);

    public function getStorageReaderMap();

    public function getStorageReader($reader_name);

    public function getProjectionReaderByType(ProjectionTypeInterface $projection_type);

    public function getFinderMap();

    public function getFinder($finder_name);

    public function getProjectionFinderByType(ProjectionTypeInterface $projection_type);

    public function getQueryServiceMap();

    public function getQueryService($query_service_name);

    public function getProjectionQueryServiceByType(ProjectionTypeInterface $projection_type);

    public function getUnitOfWorkMap();

    public function getUnitOfWork($uow_name);

    public function writeTo($writer_name, $payload, array $settings = []);

    public function readFrom($reader_name, $identifier, array $settings = []);

    public function deleteFrom($writer_name, $identifier, array $settings = []);
}
