<?php

namespace Honeybee\Tests;

use Auryn\Injector;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Honeybee\ServiceLocator;
use Honeybee\Tests\TestCase;
use Mockery;
use Trellis\Common\Options;

class ServiceLocatorTest extends TestCase
{
    public function testGetService()
    {
        $service_locator = $this->mockServiceLocator('mock_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->get('mock_service'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetServiceNotFound()
    {
        $service_locator = new ServiceLocator(new Injector, new ServiceDefinitionMap);
        $service_locator->get('non-existent');
    }

    public function testGetLogger()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.logger');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getLogger());
    }

    public function testGetFilesystemService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.filesystem_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getFilesystemService());
    }

    public function testGetTaskService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.model.task_service');
        $this->assertInstanceOf(
            ServiceDefinitionInterface::CLASS,
            $service_locator->get('honeybee.model.task_service')
        );
    }

    public function testGetExpressionService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.expression_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getExpressionService());
    }

    public function testGetDataAccessService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.data_access_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getDataAccessService());
    }

    public function testGetWorkflowService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.workflow_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getWorkflowService());
    }

    public function testGetFixtureService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.fixture_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getFixtureService());
    }

    public function testGetJobService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.job_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getJobService());
    }

    public function testGetEventBus()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.event_bus');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getEventBus());
    }

    public function testGetCommandBus()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.command_bus');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getCommandBus());
    }

    public function testGetMigrationService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.migration_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getMigrationService());
    }

    public function testGetConnectorService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.connector_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getConnectorService());
    }

    public function testMake()
    {
        $injector = new Injector;
        $service_locator = new ServiceLocator($injector, new ServiceDefinitionMap);
        $impl = 'Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType';
        $this->assertInstanceOf($impl, $service_locator->make($impl));
    }

    private function mockServiceLocator($name)
    {
        $mock_service_definition = Mockery::mock(ServiceDefinitionInterface::CLASS);
        $mock_service_definition->shouldReceive('getClass')->once()->withNoArgs()->andReturn('mock_service_class');
        $mock_injector = Mockery::mock(Injector::CLASS);
        $mock_injector->shouldReceive('make')->once()->with('mock_service_class')->andReturn($mock_service_definition);
        $service_map = new ServiceDefinitionMap;
        $service_map->setItem($name, $mock_service_definition);
        return new ServiceLocator($mock_injector, $service_map);
    }
}
