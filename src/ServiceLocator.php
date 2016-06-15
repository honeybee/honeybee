<?php

namespace Honeybee;

use Auryn\Injector as DiContainer;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\ServiceDefinitionMap;
use ReflectionClass;
use Trellis\Common\ObjectInterface;

class ServiceLocator implements ServiceLocatorInterface
{
    protected $di_container;

    protected $aggregate_root_type_map;

    protected $projection_type_map;

    protected $service_map;

    public static function buildServiceKey(ObjectInterface $object, $service_type)
    {
        $reflection_class = new ReflectionClass($object);
        $namespace_parts = explode('\\', $reflection_class->getNamespaceName());

        if (count($namespace_parts) < 3) {
            throw new RuntimeError('Missing min. namespace-depth of 3. Unable to build a valid service-key.');
        }

        $vendor_name = array_shift($namespace_parts);
        $package_name = array_shift($namespace_parts);
        $entity_type = array_shift($namespace_parts);

        return sprintf(
            '%s.%s.%s.%s',
            strtolower($vendor_name),
            StringToolkit::asSnakeCase($package_name),
            StringToolkit::asSnakeCase($entity_type),
            $service_type
        );
    }

    public function __construct(
        DiContainer $di_container,
        ServiceDefinitionMap $service_map,
        AggregateRootTypeMap $aggregate_root_type_map,
        ProjectionTypeMap $projection_type_map
    ) {
        $this->di_container = $di_container;
        $this->service_map = $service_map;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->projection_type_map = $projection_type_map;
    }

    public function getService($service_key)
    {
        if (!$this->service_map->hasKey($service_key)) {
            throw new RuntimeError(sprintf('No service found for given service-key: "%s".', $service_key));
        }

        $service_definition = $this->service_map->getItem($service_key);

        return $this->di_container->make($service_definition->getClass());
    }

    public function getUrlGenerator()
    {
        return $this->getService('honeybee.ui.url_generator');
    }

    public function getTranslator()
    {
        return $this->getService('honeybee.ui.translator');
    }

    public function getLogger()
    {
        return $this->getService('honeybee.infrastructure.logger');
    }

    public function getFilesystemService()
    {
        return $this->getService('honeybee.infrastructure.filesystem_service');
    }

    public function getTaskService()
    {
        return $this->getService('honeybee.model.task_service');
    }

    public function getExpressionService()
    {
        return $this->getService('honeybee.infrastructure.expression_service');
    }

    public function getAuthenticationService()
    {
        return $this->getService('honeybee.infrastructure.auth_service');
    }

    public function getAclService()
    {
        return $this->getService('honeybee.infrastructure.acl_service');
    }

    public function getPermissionService()
    {
        return $this->getService('honeybee.infrastructure.permission_service');
    }

    public function getDataAccessService()
    {
        return $this->getService('honeybee.infrastructure.data_access_service');
    }

    public function getActivityService()
    {
        return $this->getService('honeybee.ui.activity_service');
    }

    public function getOutputFormatService()
    {
        return $this->getService('honeybee.ui.output_format_service');
    }

    public function getNavigationService()
    {
        return $this->getService('honeybee.ui.navigation_service');
    }

    public function getRendererService()
    {
        return $this->getService('honeybee.ui.renderer_service');
    }

    public function getViewConfigService()
    {
        return $this->getService('honeybee.ui.view_config_service');
    }

    public function getViewTemplateService()
    {
        return $this->getService('honeybee.ui.view_template_service');
    }

    public function getConnectorService()
    {
        return $this->getService('honeybee.infrastructure.connector_service');
    }

    public function getMigrationService()
    {
        return $this->getService('honeybee.infrastructure.migration_service');
    }

    public function getTemplateRenderer()
    {
        return $this->getService('honeybee.infrastructure.template_renderer');
    }

    public function getCommandBus()
    {
        return $this->getService('honeybee.infrastructure.command_bus');
    }

    public function getEventBus()
    {
        return $this->getService('honeybee.infrastructure.event_bus');
    }

    public function getProcessManager()
    {
        return $this->getService('honeybee.infrastructure.process_manager');
    }

    public function getProcessMap()
    {
        return $this->getService('honeybee.infrastructure.process_map');
    }

    public function getJobService()
    {
        return $this->getService('honeybee.infrastructure.job_service');
    }

    public function getFixtureService()
    {
        return $this->getService('honeybee.infrastructure.fixture_service');
    }

    public function getProjectionTypeMap()
    {
        return $this->projection_type_map;
    }

    public function getAggregateRootTypeMap()
    {
        return $this->aggregate_root_type_map;
    }

    public function getAggregateRootTypeByPrefix($aggregate_root_name)
    {
        if (!isset($this->aggregate_root_type_map[$aggregate_root_name])) {
            throw new RuntimeError(
                "Invalid aggregate-root-type name given: " . $aggregate_root_name
            );
        }

        return $this->aggregate_root_type_map[$aggregate_root_name];
    }

    public function getProjectionTypeByPrefix($projection_type_prefix)
    {
        if (!isset($this->projection_type_map[$projection_type_prefix])) {
            throw new RuntimeError(
                "Invalid projection-type name given: " . $projection_type_prefix
            );
        }

        return $this->projection_type_map[$projection_type_prefix];
    }

    public function createEntity($implementor, array $state = [])
    {
        return $this->di_container->make($implementor, $state);
    }

    protected function resolveAggregateRootType($aggregate_root_type)
    {
        if (is_string($aggregate_root_type)) {
            $aggregate_root_type = $this->getAggregateRootTypeByPrefix($aggregate_root_type);
        } elseif (!$aggregate_root_type instanceof AggregateRootTypeInterface) {
            throw new RuntimeError(
                'Invalid argument type given for $aggregate_root_type.'.
                'Make sure to pass either a valid name or AggregateRootTypeInterface instance.'
            );
        }

        return $aggregate_root_type;
    }

    protected function resolveProjectionType($projection_type)
    {
        if (is_string($projection_type)) {
            $projection_type = $this->getProjectionTypeByPrefix($projection_type);
        } elseif (!$projection_type instanceof ProjectionTypeInterface) {
            throw new RuntimeError(
                'Invalid argument type given for $projection_type.'.
                'Make sure to pass either a valid name or ProjectionTypeInterface instance.'
            );
        }

        return $projection_type;
    }
}
