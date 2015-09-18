<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee\Ui\Activity;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\ArrayToolkit;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Ui\Renderer\ActivityMapRenderer;

class HtmlActivityMapRenderer extends ActivityMapRenderer
{
    protected function validate()
    {
        parent::validate();

        if ($this->hasOption('dropdown_label') && !$this->getOption('as_dropdown', false)) {
            throw new RuntimeError('Option "dropdown_label" is only valid when option "as_dropdown" is true.');
            $this->settings['as_dropdown'] = true;
        }
    }

    protected function getDefaultTemplateIdentifier()
    {
        if ($this->getOption('as_dropdown', false)) {
            return $this->output_format->getName() . '/activity_map/as_dropdown.twig';
        } elseif ($this->getOption('as_list', false)) {
            return $this->output_format->getName() . '/activity_map/as_list.twig';
        }

        return $this->output_format->getName() . '/activity_map/as_splitbutton.twig';
    }

    protected function getTemplateParameters()
    {
        // activities in a dropdown or similar should be plain activities instead of buttons
        $settings = $this->settings->toArray();
        $settings['plain_activity'] = true;
        $this->settings = new Settings($settings);

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

        $default_activity = $activity_map->getItem($default_activity_name);
        $default_activity_label = $default_activity->getLabel();
        if (empty($default_activity_label)) {
            $default_activity_label = sprintf('%s.label', $default_activity->getName());
        }

        $dropdown_label = $this->_(
            $this->getOption(
                'dropdown_label',
                $default_activity_label
            )
        );

        $rendered_activities = [];
        foreach ($activity_map as $activity) {
            $name = $activity->getName();

            if (in_array($name, $hidden_activity_names) && ($name !== $default_activity_name)) {
                continue; // don't render activities that should not be displayed
            }

            $additional_payload = [
                'subject' => $activity
            ];

            // workflow activities need an 'resource' or 'module' to generate the url correctly, leaky abstraction \o/
            if ($this->hasPayload('resource')) {
                $additional_payload['resource'] = $this->payload->get('resource');
            } elseif ($this->hasPayload('module')) {
                $additional_payload['module'] = $this->payload->get('module');
            }

            $activity_renderer_config = $this->view_config_service->getRendererConfig(
                $this->getOption('view_scope'),
                $this->output_format,
                'activity.' . $activity->getName(),
                $this->config->toArray()
            );
            $rendered_activities[$name] = $this->renderer_service->renderSubject(
                $activity,
                $this->output_format,
                $activity_renderer_config,
                $additional_payload,
                $this->settings
            );
        }

        // put default activity to top as that should be the primary activity
        ArrayToolkit::moveToTop($rendered_activities, $default_activity_name);

        $params['name'] = $this->getOption('name');
        $params['tag'] = $this->getOption('tag');
        $params['html_attributes'] = $this->getOption('html_attributes');

        $default_css = 'activity-map';
        $params['css'] = $this->getOption('css', $default_css);

        $params['trigger_id'] = $this->getOption('trigger_id');
        $params['trigger_css'] = $this->getOption('trigger_css');
        $params['trigger_html_attributes'] = $this->getOption('trigger_html_attributes');

        $params['toggle_content'] = $this->getOption('toggle_content');
        $params['toggle_css'] = $this->getOption('css');
        $params['toggle_html_attributes'] = $this->getOption('toggle_html_attributes');

        $default_label = $dropdown_label;
        if (!$this->getOption('as_dropdown', false)) {
            $default_label = $rendered_activities[$default_activity_name];
        }
        $params['default_content'] = $this->getOption('default_content', $default_label);
        $params['default_css'] = $this->getOption('default_css');
        $params['default_html_attributes'] = $this->getOption('default_html_attributes');

        // don't render primary activity in (more) activities list when no dropdown label was given
        // thus, when a dropdown_label was specified the (more) activities are ALL activities
        if (!$this->getOption('dropdown_label', false)) {
            if (!$this->getOption('as_list')) { // when as_list is given, then don't remove, as ALL should be shown
                unset($rendered_activities[$default_activity_name]);
            }
        }

        $params['more_css'] = $this->getOption('more_css');
        $params['more_html_attributes'] = $this->getOption('more_html_attributes');
        $params['more_activities'] = $this->getOption('more_activities', $rendered_activities);

        $params['toggle_disabled'] = $this->getOption('toggle_disabled', false);
        if (!count($params['more_activities'])) {
            $params['toggle_disabled'] = true;
        }

        return $params;
    }
}
