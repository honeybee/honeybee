<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Ui\Navigation\NavigationInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Common\Util\StringToolkit;

abstract class NavigationRenderer extends Renderer
{
    const STATIC_TRANSLATION_PATH = "navigations";

    protected function validate()
    {
        if (!$this->getPayload('subject') instanceof NavigationInterface) {
            throw new RuntimeError(
                sprintf('Payload "subject" must implement "%s".', NavigationInterface::CLASS)
            );
        }
    }

    protected function doRender()
    {
        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
    }

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/navigation/main.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['navigation_data'] = $this->prepareNavigationData($this->getPayload('subject'));

        return $params;
    }

    protected function prepareNavigationData(NavigationInterface $navigation)
    {
        $view_scope = $this->getOption('view_scope');

        $navigation_data = [
            'name' => $navigation->getName(),
            'groups' => []
        ];

        $activity_renderer_config = [];
        if ($this->getOption('propagate_view_scope', false)) {
            $activity_renderer_config['view_scope'] = $view_scope;
        }

        foreach ($navigation->getNavigationGroups() as $navigation_group) {
            $group_data = [
                'name' => $navigation_group->getName(),
                'items' => []
            ];

            foreach ($navigation_group->getNavigationItems() as $navigation_item) {
                $activity = $navigation_item->getActivity();

                $activity_renderer_settings = $this->view_config_service->getRendererConfig(
                    $view_scope,
                    $this->output_format,
                    $activity->getName(),
                    []
                )->toArray();

                $renderer_config = new ArrayConfig(
                    array_replace_recursive($activity_renderer_config, $activity_renderer_settings)
                );

                $group_data['items'][] = [
                    'rendered_activity' => $this->renderer_service->renderSubject(
                        $activity,
                        $this->output_format,
                        $renderer_config
                    )
                ];
            }
            $translation_key = sprintf('%s.%s', $navigation->getName(), $navigation_group->getName());
            $navigation_data['groups'][$translation_key] = $group_data;
        }

        return $navigation_data;
    }

    protected function getDefaultTranslationDomain()
    {
        return sprintf(
            '%s.%s',
            parent::getDefaultTranslationDomain(),
            self::STATIC_TRANSLATION_PATH
        );
    }
}
