<?php

namespace Honeybee\Infrastructure\DataAccess;

interface DataAccessServiceInterface
{
    public function getStorageWriterMap();

    public function getStorageWriter($writer_name);

    public function getStorageReaderMap();

    public function getStorageReader($reader_name);

    public function getFinderMap();

    public function getFinder($finder_name);

    public function getQueryServiceMap();

    public function getQueryService($query_service_name);

    public function getUnitOfWorkMap();

    public function getUnitOfWork($uow_name);

    public function writeTo($writer_name, $payload, array $settings = []);

    public function readFrom($reader_name, $identifier, array $settings = []);

    public function deleteFrom($writer_name, $identifier, array $settings = []);
}
