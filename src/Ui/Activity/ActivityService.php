<?php

namespace Honeybee\Ui\Activity;

use AgaviContext;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\GenericScopeKey;
use Honeybee\Common\ScopeKeyInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Ui\UrlGeneratorInterface;
use QL\UriTemplate\UriTemplate;
use Trellis\Common\Object;

class ActivityService extends Object implements ActivityServiceInterface
{
    const WORKFLOW_SCOPE_PATTERN = '%s.%s';

    protected $aggregate_root_type_map;

    protected $activity_container_map;

    protected $workflow_activity_service;

    protected $is_initialized = false;

    public function __construct(
        WorkflowActivityService $workflow_activity_service,
        ActivityContainerMap $activity_container_map,
        AggregateRootTypeMap $aggregate_root_type_map,
        UrlGeneratorInterface $url_generator
    ) {
        $this->activity_container_map = $activity_container_map;
        $this->workflow_activity_service = $workflow_activity_service;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->url_generator = $url_generator;
    }

    public function getContainers()
    {
        $this->ensureIsInitialize();

        return $this->activity_container_map;
    }

    public function getActivityMap($scope)
    {
        return $this->getContainer($scope)->getActivityMap();
    }

    /**
     * Returns an activity of the given name from the given activity container scope.
     *
     * Only activities allowed for the current user are returned.
     *
     * @param string $scope activity container scope key
     * @param string $name activity name
     *
     * @return ActivityInterface|null
     */
    public function getActivity($scope, $name)
    {
        return $this->getContainer($scope)->getActivityByName($name);
    }

    public function hasContainer($scope)
    {
        $this->ensureIsInitialize();
        $scope_key = $this->resolveScopeKey($scope);

        return $this->getContainers()->hasKey($scope_key->getScopeKey());
    }

    public function getContainer($scope)
    {
        $this->ensureIsInitialize();

        $scope_key = $this->resolveScopeKey($scope);
        $container = $this->getContainers()->getItem($scope_key->getScopeKey());
        if (!$container) {
            throw new RuntimeError(
                sprintf(
                    'No activity container found for scope key "%s" from given scope: %s',
                    $scope_key->getScopeKey(),
                    is_object($scope) ? get_class($scope) : print_r($scope, true)
                )
            );
        }

        $activity_map = new ActivityMap();
        $user = AgaviContext::getInstance()->getUser();
        foreach ($container->getActivityMap() as $activity_name => $activity) {
            if ($user->isAllowed($scope, $activity_name)) {
                $activity_map->setItem($activity_name, $activity);
            }
        }

        $container_state = [ 'scope' => $scope_key->getScopeKey(), 'activity_map' => $activity_map ];

        return new ActivityContainer($container_state);
    }

    /**
     * Returns the activity's URI with the given parameters/options applied. Takes the type of the Url
     * of the activity into account.
     *
     * @param ActivityInterface $activity activity (with a URL and parameters)
     * @param array $parameters parameters to merge/replace into the default URL parameters of the activity
     * @param array $options options to take into account when generating the URL
     *
     * @return string resulting URI
     */
    public function getUri(ActivityInterface $activity, array $parameters = [], array $options = [])
    {
        $url = $activity->getUrl();
        $params = array_replace_recursive($url->getParameters(), $parameters);

        $resulting_uri = '';
        if ($url->getType() === Url::TYPE_ROUTE) {
            $resulting_uri = $this->url_generator->generateUrl($url->getValue(), $params, $options);
        } elseif ($url->getType() === Url::TYPE_URI_TEMPLATE) {
            $uri_template = new UriTemplate($url->getValue());
            $resulting_uri = $uri_template->expand($params);
        } else {
            $resulting_uri = $url->__toString(); // TODO apply params here as well as query params? syntax?
        }

        return $resulting_uri;
    }

    protected function resolveScopeKey($scope)
    {
        if ($scope instanceof ScopeKeyInterface) {
            $scope_key = $scope;
        } else if (is_string($scope)) {
            $scope_key = new GenericScopeKey($scope);
        } else {
            throw new RuntimeError(
                sprintf(
                    'Unable to resolve given scope to a valid instance of "%s". Scope given: %s',
                    ScopeKey::CLASS,
                    is_object($scope) ? get_class($scope) : gettype($scope)
                )
            );
        }

        return $scope_key;
    }

    protected function ensureIsInitialize()
    {
        if (!$this->is_initialized) {
            $this->addWorkflowActivities();
            $this->is_initialized = true;
        }
    }

    protected function addWorkflowActivities()
    {
        // magic! here we create activities that don't come from the activities.xml,
        // but are derived from the workflows.xml in order to model what we call "workflow activities".
        foreach ($this->aggregate_root_type_map as $aggregate_root_type) {
            $workflow_activities_map = $this->workflow_activity_service->getActivities($aggregate_root_type);

            foreach ($workflow_activities_map as $workflow_step => $workflow_activities) {
                $scope = sprintf(self::WORKFLOW_SCOPE_PATTERN, $aggregate_root_type->getPrefix(), $workflow_step);

                if ($this->activity_container_map->hasKey($scope)) {
                    $container = $this->activity_container_map->getItem($scope);
                    $container->getActivityMap()->append($workflow_activities);
                } else {
                    $container_state = [ 'scope' => $scope, 'activity_map' => $workflow_activities ];
                    $workflow_activity_container = new ActivityContainer($container_state);
                    $this->activity_container_map->setItem($scope, $workflow_activity_container);
                }
            }
        }
    }
}
