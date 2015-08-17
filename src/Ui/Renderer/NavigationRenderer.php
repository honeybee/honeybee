<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Ui\Navigation\NavigationInterface;
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
        $navigation_data = [
            'name' => $navigation->getName(),
            'groups' => []
        ];

        foreach ($navigation->getNavigationGroups() as $navigation_group) {
            $group_data = [
                'name' => $navigation_group->getName(),
                'items' => []
            ];

            foreach ($navigation_group->getNavigationItems() as $navigation_item) {
                $activity = $navigation_item->getActivity();

                $group_data['items'][] = [
                    'rendered_activity' => $this->renderer_service->renderSubject(
                        $activity,
                        $this->output_format,
                        $this->config
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
        // @todo Include also the navigation name (cause it is possible
        // to have multiple navigations) into the translation domain

        return sprintf(
            '%s.%s',
            parent::getDefaultTranslationDomain(),
            self::STATIC_TRANSLATION_PATH
        );
    }
}
