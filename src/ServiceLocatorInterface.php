<?php

namespace Honeybee;

use Trellis\Common\ObjectInterface;

/**
 * Interface for convenience wrapper classes that provide access
 * to well known and often used Honeybee services.
 */
interface ServiceLocatorInterface
{
    public function getService($service_key);

    public function getAggregateRootTypeMap();
    public function getProjectionTypeMap();

    public function getAuthenticationService();
    public function getAclService();
    public function getPermissionService();

    public function getCommandBus();
    public function getEventBus();
    public function getProcessManager();

    public function getConnectorService();
    public function getDataAccessService();
    public function getJobService();
    public function getMigrationService();
    public function getTaskService();

    public function getExpressionService();
    public function getFilesystemService();
    public function getFixtureService();

    public function getUrlGenerator();
    public function getTranslator();

    public function getActivityService();
    public function getNavigationService();
    public function getOutputFormatService();
    public function getRendererService();
    public function getTemplateRenderer();
    public function getViewConfigService();
    public function getViewTemplateService();

    public function createEntity($implementor, array $state = []);
    public function getLogger();

    public static function buildServiceKey(ObjectInterface $object, $service_type);
}
