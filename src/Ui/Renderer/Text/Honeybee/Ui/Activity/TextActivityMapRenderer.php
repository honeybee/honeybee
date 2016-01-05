<?php

namespace Honeybee\Ui\Renderer\Text\Honeybee\Ui\Activity;

use Honeybee\Common\Util\ArrayToolkit;
use Honeybee\Ui\Renderer\ActivityMapRenderer;

class TextActivityMapRenderer extends ActivityMapRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return 'text/activity/activity_map.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $original_activity_map = $this->getPayload('subject');

        if ($original_activity_map->isEmpty()) {
            return $params;
        }

        $hidden_activity_names = (array)$this->getOption('hidden_activity_names', []);

        // remove all activities that are excluded via config/settings
        $activity_map = $original_activity_map->filter(
            function ($activity) use ($hidden_activity_names) {
                if (in_array($activity->getName(), $hidden_activity_names)) {
                    return false;
                }
                return true;
            }
        );

        if ($activity_map->isEmpty()) {
            return $params;
        }

        // determine which of the remaining activities should be the primary/current one
        $default_activity_name = $this->getOption('default_activity_name', '');
        if (!$activity_map->hasKey($default_activity_name)) {
            $default_activity_name = $activity_map->getKeys()[0];
        }

        $rendered_activities = [];
        foreach ($activity_map as $activity) {
            $name = $activity->getName();

            if (in_array($name, $hidden_activity_names) && ($name !== $default_activity_name)) {
                continue; // don't render activities that should not be displayed
            }

            $additional_payload = [
                'subject' => $activity
            ];

            // workflow activities need an 'resource' to generate the url correctly
            if ($this->hasPayload('resource')) {
                $additional_payload['resource'] = $this->payload->get('resource');
            } elseif ($this->hasPayload('module')) {
                $additional_payload['module'] = $this->payload->get('module');
            }

            $rendered_activities[$name] = $this->renderer_service->renderSubject(
                $activity,
                $this->output_format,
                $this->config,
                $additional_payload
            );
        }

        // put default activity to top as that should be the primary activity
        ArrayToolkit::moveToTop($rendered_activities, $default_activity_name);

        $params['rendered_activities'] = $rendered_activities;

        return $params;
    }
}
