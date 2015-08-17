<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Ui\Activity\ActivityInterface;

class GenericSubjectRenderer extends Renderer
{
    protected $activity_service;

    protected function validate()
    {
        // TODO do we need to force a subject?


        $activity = $this->getPayload('activity');
        if ($activity && !$activity instanceof ActivityInterface) {
            throw new RuntimeError(
                sprintf('Payload "activity" must be given and implement "%s".', ActivityInterface::CLASS)
            );
        }
    }

    protected function doRender()
    {
        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
    }

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/subject/as_itemlist_item_cell.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['html_attributes'] = $this->getOption('html_attributes', []);
        $params['field_name'] = $this->getOption('field_name', 'subject');
        $params = array_merge($params, $this->getOptions());
        if ($this->hasPayload('activity')) {
            $activity = $this->getPayload('activity')->toArray();
            $params['activity'] = $activity;
            $params['rendered_activity'] = $this->renderer_service->renderSubject(
                $activity,
                $this->output_format,
                $this->config,
                [],
                $this->settings
            );
        }

        $css = (string)$this->getOption('field_css', '');
        $params['css'] = $css;

        return $params;
    }
}
