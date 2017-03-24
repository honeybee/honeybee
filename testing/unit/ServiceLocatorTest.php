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
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getService('mock_service'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetServiceNotFound()
    {
        $service_locator = new ServiceLocator(
            new Injector,
            new ServiceDefinitionMap,
            new AggregateRootTypeMap,
            new ProjectionTypeMap
        );

        $service_locator->getService('non-existent');
    }

    public function testGetUrlGenerator()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.url_generator');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getUrlGenerator());
    }

    public function testGetTranslator()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.translator');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getTranslator());
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
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getTaskService());
    }

    public function testGetExpressionService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.expression_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getExpressionService());
    }

    public function testGetAuthenticationService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.auth_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getAuthenticationService());
    }

    public function testGetAclService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.acl_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getAclService());
    }

    public function testGetPermissionService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.permission_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getPermissionService());
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

    public function testGetProcessMap()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.process_map');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getProcessMap());
    }

    public function testGetProcessManager()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.process_manager');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getProcessManager());
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

    public function testGetTemplateRenderer()
    {
        $service_locator = $this->mockServiceLocator('honeybee.infrastructure.template_renderer');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getTemplateRenderer());
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

    public function testGetViewTemplateService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.view_template_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getViewTemplateService());
    }

    public function testGetViewConfigService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.view_config_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getViewConfigService());
    }

    public function testGetRendererService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.renderer_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getRendererService());
    }

    public function testGetNavigationService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.navigation_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getNavigationService());
    }

    public function testGetOutputFormatService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.output_format_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getOutputFormatService());
    }

    public function testGetActivityService()
    {
        $service_locator = $this->mockServiceLocator('honeybee.ui.activity_service');
        $this->assertInstanceOf(ServiceDefinitionInterface::CLASS, $service_locator->getActivityService());
    }

    public function testGetProjectionTypeMap()
    {
        $projection_type_map = new ProjectionTypeMap;
        $service_locator = new ServiceLocator(
            new Injector,
            new ServiceDefinitionMap,
            new AggregateRootTypeMap,
            $projection_type_map
        );

        $this->assertEquals($projection_type_map, $service_locator->getProjectionTypeMap());
    }

    public function testGetAggregateRootTypeMap()
    {
        $aggregate_root_type_map = new AggregateRootTypeMap;
        $service_locator = new ServiceLocator(
            new Injector,
            new ServiceDefinitionMap,
            $aggregate_root_type_map,
            new ProjectionTypeMap
        );

        $this->assertEquals($aggregate_root_type_map, $service_locator->getAggregateRootTypeMap());
    }

    public function testGetDic()
    {
        $injector = new Injector;
        $service_locator = new ServiceLocator(
            $injector,
            new ServiceDefinitionMap,
            new AggregateRootTypeMap,
            new ProjectionTypeMap
        );

        $this->assertEquals($injector, $service_locator->getDic());
    }

    private function mockServiceLocator($name)
    {
        $mock_service_definition = Mockery::mock(ServiceDefinitionInterface::CLASS);
        $mock_service_definition->shouldReceive('getClass')->once()->withNoArgs()->andReturn('mock_service_class');
        $mock_injector = Mockery::mock(Injector::CLASS);
        $mock_injector->shouldReceive('make')->once()->with('mock_service_class')->andReturn($mock_service_definition);

        $service_map = new ServiceDefinitionMap;
        $service_map->setItem($name, $mock_service_definition);

        return new ServiceLocator(
            $mock_injector,
            $service_map,
            new AggregateRootTypeMap,
            new ProjectionTypeMap
        );
    }
}
