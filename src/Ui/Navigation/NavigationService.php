<?php

namespace Honeybee\Ui\Navigation;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Ui\Activity\ActivityServiceInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Trellis\Common\Object;

class NavigationService extends Object implements NavigationServiceInterface
{
    protected $navigations_config;

    protected $default_navigation_name;

    protected $navigation_map;

    protected $activity_service;

    public function __construct(ConfigInterface $navigations_config, $default_navigation, ActivityServiceInterface $activity_service)
    {
        $this->navigations_config = $navigations_config;
        $this->default_navigation_name = $default_navigation;
        $this->activity_service = $activity_service;

        $this->navigation_map = new NavigationMap();
    }

    /**
     * @return NavigationInterface
     */
    public function getNavigation($navigation_name = null)
    {
        $navigation_name = $navigation_name ?: $this->default_navigation_name;

        if (!$this->navigation_map->hasKey($navigation_name)) {
            $this->buildNavigation($navigation_name);
        }

        return $this->navigation_map->getItem($navigation_name);
    }

    /**
     * @return string name of the default navigation
     */
    public function getDefaultNavigationName()
    {
        return $this->default_navigation_name;
    }

    protected function buildNavigation($navigation_name)
    {
        if (!$this->navigations_config->has($navigation_name)) {
            throw new RuntimeError(sprintf("Given navigation name '%s' has not been configured.", $navigation_name));
        }

        $navigation_config = $this->navigations_config->get($navigation_name);
        $navigation_group_map = new NavigationGroupMap();

        foreach ($navigation_config['groups'] as $group_name => $navigation_group_config) {
            $navigation_item_list = new NavigationItemList();

            foreach ($navigation_group_config['items'] as $navigation_item_config) {
                $container = $this->activity_service->getContainer($navigation_item_config['scope']);
                $activity = $container->getActivityByName($navigation_item_config['activity']);
                if ($activity) {
                    $navigation_item_list->addItem(new NavigationItem($activity));
                }
            }

            $group_settings = $navigation_group_config->get('settings')->toArray();

            $navigation_group = new NavigationGroup($group_name, $navigation_item_list, $group_settings);
            $navigation_group_map->setItem($group_name, $navigation_group);
        }

        $this->navigation_map->setItem($navigation_name, new Navigation($navigation_name, $navigation_group_map));
    }
}
