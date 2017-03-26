<?php

namespace Honeybee\Tests\DataAccess;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessService;
use Honeybee\Infrastructure\DataAccess\Finder\FinderInterface;
use Honeybee\Infrastructure\DataAccess\Finder\FinderMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderMap;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\DataAccess\UnitOfWork\UnitOfWorkInterface;
use Honeybee\Infrastructure\DataAccess\UnitOfWork\UnitOfWorkMap;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Tests\TestCase;
use Mockery;

class DataAccessServiceTest extends TestCase
{
    public function testGetStorageWriterMap()
    {
        $storage_writer_map = new StorageWriterMap;
        $data_access_service = $this->makeDAService($storage_writer_map);

        $this->assertSame($storage_writer_map, $data_access_service->getStorageWriterMap());
    }

    public function testGetStorageWriter()
    {
        $mock_storage_writer = Mockery::mock(StorageWriterInterface::CLASS);
        $storage_writer_map = new StorageWriterMap(['mock_writer' => $mock_storage_writer]);
        $data_access_service = $this->makeDAService($storage_writer_map);

        $this->assertSame($mock_storage_writer, $data_access_service->getStorageWriter('mock_writer'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetStorageWriterMissing()
    {
        $data_access_service = $this->makeDAService();
        $data_access_service->getStorageWriter('mock_writer');
    }

    public function testGetProjectionWriterByType()
    {
        $mock_standard_projection_writer = Mockery::mock(StorageWriterInterface::CLASS);
        $mock_other_projection_writer = Mockery::mock(StorageWriterInterface::CLASS);
        $mock_projection_type = Mockery::mock(ProjectionTypeInterface::CLASS);
        $mock_projection_type->shouldReceive('getVariantPrefix')->twice()->withNoArgs()->andReturns(
            'mock_type::projection.standard',
            'mock_type::projection.other'
        );
        $storage_writer_map = new StorageWriterMap([
            'mock_type::projection.standard::view_store::writer' => $mock_standard_projection_writer,
            'mock_type::projection.other::view_store::writer' => $mock_other_projection_writer
        ]);
        $data_access_service = $this->makeDAService($storage_writer_map);

        $this->assertSame(
            $mock_standard_projection_writer,
            $data_access_service->getProjectionWriterByType($mock_projection_type)
        );

        $this->assertSame(
            $mock_other_projection_writer,
            $data_access_service->getProjectionWriterByType($mock_projection_type)
        );
    }

    public function testGetStorageReaderMap()
    {
        $storage_reader_map = new StorageReaderMap;
        $data_access_service = $this->makeDAService(null, $storage_reader_map);

        $this->assertSame($storage_reader_map, $data_access_service->getStorageReaderMap());
    }

    public function testGetStorageReader()
    {
        $mock_storage_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $storage_reader_map = new StorageReaderMap(['mock_reader' => $mock_storage_reader]);
        $data_access_service = $this->makeDAService(null, $storage_reader_map);

        $this->assertSame($mock_storage_reader, $data_access_service->getStorageReader('mock_reader'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetStorageReaderMissing()
    {
        $data_access_service = $this->makeDAService();
        $data_access_service->getStorageReader('mock_reader');
    }

    public function testGetProjectionReaderByType()
    {
        $mock_standard_projection_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_other_projection_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_projection_type = Mockery::mock(ProjectionTypeInterface::CLASS);
        $mock_projection_type->shouldReceive('getVariantPrefix')->twice()->withNoArgs()->andReturns(
            'mock_type::projection.standard',
            'mock_type::projection.other'
        );
        $storage_reader_map = new StorageReaderMap([
            'mock_type::projection.standard::view_store::reader' => $mock_standard_projection_reader,
            'mock_type::projection.other::view_store::reader' => $mock_other_projection_reader
        ]);
        $data_access_service = $this->makeDAService(null, $storage_reader_map);

        $this->assertSame(
            $mock_standard_projection_reader,
            $data_access_service->getProjectionReaderByType($mock_projection_type)
        );

        $this->assertSame(
            $mock_other_projection_reader,
            $data_access_service->getProjectionReaderByType($mock_projection_type)
        );
    }

    public function testGetFinderMap()
    {
        $finder_map = new FinderMap;
        $data_access_service = $this->makeDAService(null, null, $finder_map);

        $this->assertSame($finder_map, $data_access_service->getFinderMap());
    }

    public function testGetFinder()
    {
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $finder_map = new FinderMap(['mock_finder' => $mock_finder]);
        $data_access_service = $this->makeDAService(null, null, $finder_map);

        $this->assertSame($mock_finder, $data_access_service->getFinder('mock_finder'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetFinderMissing()
    {
        $data_access_service = $this->makeDAService();
        $data_access_service->getFinder('mock_finder');
    }

    public function testGetProjectionFinderByType()
    {
        $mock_standard_projection_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_other_projection_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_projection_type = Mockery::mock(ProjectionTypeInterface::CLASS);
        $mock_projection_type->shouldReceive('getVariantPrefix')->twice()->withNoArgs()->andReturns(
            'mock_type::projection.standard',
            'mock_type::projection.other'
        );
        $finder_map = new FinderMap([
            'mock_type::projection.standard::view_store::finder' => $mock_standard_projection_finder,
            'mock_type::projection.other::view_store::finder' => $mock_other_projection_finder
        ]);
        $data_access_service = $this->makeDAService(null, null, $finder_map);

        $this->assertSame(
            $mock_standard_projection_finder,
            $data_access_service->getProjectionFinderByType($mock_projection_type)
        );

        $this->assertSame(
            $mock_other_projection_finder,
            $data_access_service->getProjectionFinderByType($mock_projection_type)
        );
    }

    public function testGetQueryServiceMap()
    {
        $query_service_map = new QueryServiceMap;
        $data_access_service = $this->makeDAService(null, null, null, $query_service_map);

        $this->assertSame($query_service_map, $data_access_service->getQueryServiceMap());
    }

    public function testGetQueryService()
    {
        $mock_query_service = Mockery::mock(QueryServiceInterface::CLASS);
        $query_service_map = new QueryServiceMap(['mock_query_service' => $mock_query_service]);
        $data_access_service = $this->makeDAService(null, null, null, $query_service_map);

        $this->assertSame($mock_query_service, $data_access_service->getQueryService('mock_query_service'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetQueryServiceMissing()
    {
        $data_access_service = $this->makeDAService();
        $data_access_service->getQueryService('mock_query_service');
    }

    public function testGetProjectionQueryServiceByType()
    {
        $mock_standard_projection_query_service = Mockery::mock(QueryServiceInterface::CLASS);
        $mock_other_projection_query_service = Mockery::mock(QueryServiceInterface::CLASS);
        $mock_projection_type = Mockery::mock(ProjectionTypeInterface::CLASS);
        $mock_projection_type->shouldReceive('getVariantPrefix')->twice()->withNoArgs()->andReturns(
            'mock_type::projection.standard',
            'mock_type::projection.other'
        );
        $query_service_map = new QueryServiceMap([
            'mock_type::projection.standard::view_store::query_service' => $mock_standard_projection_query_service,
            'mock_type::projection.other::view_store::query_service' => $mock_other_projection_query_service
        ]);
        $data_access_service = $this->makeDAService(null, null, null, $query_service_map);

        $this->assertSame(
            $mock_standard_projection_query_service,
            $data_access_service->getProjectionQueryServiceByType($mock_projection_type)
        );

        $this->assertSame(
            $mock_other_projection_query_service,
            $data_access_service->getProjectionQueryServiceByType($mock_projection_type)
        );
    }

    public function testGetUnitOfWorkMap()
    {
        $unit_of_work_map = new UnitOfWorkMap;
        $data_access_service = $this->makeDAService(null, null, null, null, $unit_of_work_map);

        $this->assertSame($unit_of_work_map, $data_access_service->getUnitOfWorkMap());
    }

    public function testGetUnitOfWork()
    {
        $mock_unit_of_work = Mockery::mock(UnitOfWorkInterface::CLASS);
        $unit_of_work_map = new UnitOfWorkMap(['mock_unit_of_work' => $mock_unit_of_work]);
        $data_access_service = $this->makeDAService(null, null, null, null, $unit_of_work_map);

        $this->assertSame($mock_unit_of_work, $data_access_service->getUnitOfWork('mock_unit_of_work'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetUnitOfWorkMissing()
    {
        $data_access_service = $this->makeDAService();
        $data_access_service->getUnitOfWork('mock_unit_of_work');
    }

    public function testWriteTo()
    {
        $payload = ['test' => 'data'];
        $settings = ['setting' => 'value'];
        $mock_storage_writer = Mockery::mock(StorageWriterInterface::CLASS);
        $mock_storage_writer->shouldReceive('write')->once()->with(
            $payload,
            Mockery::on(function (SettingsInterface $arg) use ($settings) {
                $this->assertEquals(new Settings($settings), $arg);
                return true;
            })
        );
        $storage_writer_map = new StorageWriterMap(['mock_writer' => $mock_storage_writer]);
        $data_access_service = $this->makeDAService($storage_writer_map);

        $this->assertNull($data_access_service->writeTo('mock_writer', $payload, $settings));
    }

    public function testReadFrom()
    {
        $settings = ['setting' => 'value'];
        $result = ['data' => 'content'];
        $mock_storage_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_storage_reader->shouldReceive('read')->once()->with(
            'mock_identifier',
            Mockery::on(function (SettingsInterface $arg) use ($settings) {
                $this->assertEquals(new Settings($settings), $arg);
                return true;
            })
        )->andReturn($result);
        $storage_reader_map = new StorageReaderMap(['mock_reader' => $mock_storage_reader]);
        $data_access_service = $this->makeDAService(null, $storage_reader_map);

        $this->assertEquals($result, $data_access_service->readFrom('mock_reader', 'mock_identifier', $settings));
    }

    public function testDeleteFrom()
    {
        $mock_storage_writer = Mockery::mock(StorageWriterInterface::CLASS);
        $mock_storage_writer->shouldReceive('delete')->once()->with(
            'mock_identifier',
            Mockery::on(function (SettingsInterface $arg) {
                $this->assertEquals(new Settings, $arg);
                return true;
            })
        );
        $storage_writer_map = new StorageWriterMap(['mock_writer' => $mock_storage_writer]);
        $data_access_service = $this->makeDAService($storage_writer_map);

        $this->assertNull($data_access_service->deleteFrom('mock_writer', 'mock_identifier'));
    }

    private function makeDAService($writer = null, $reader = null, $finder = null, $query_service = null, $uow = null)
    {
        return new DataAccessService(
            $writer ?: new StorageWriterMap,
            $reader ?: new StorageReaderMap,
            $finder ?: new FinderMap,
            $query_service ?: new QueryServiceMap,
            $uow ?: new UnitOfWorkMap
        );
    }
}
