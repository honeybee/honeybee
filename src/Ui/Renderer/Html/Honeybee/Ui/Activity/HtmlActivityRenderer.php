<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee\Ui\Activity;

use Honeybee\Ui\Renderer\ActivityRenderer;

class HtmlActivityRenderer extends ActivityRenderer
{
    protected function getTemplateParameters()
    {
        $activity = $this->getPayload('subject');

        $default_css = [
            'activity',
            'activity-' . strtolower($activity->getName())
        ];

        $params = [];
        $params['css'] = $this->getOption('css', $default_css);
        $params['form_id'] = $this->getOption('form_id', $activity->getSettings()->get('form_id', 'formid'));
        $params['form_parameters'] = $this->getOption('form_parameters', $activity->getUrl()->getParameters());
        $params['form_method'] = $this->getOption('form_method', ($activity->getVerb() === 'read') ? 'GET' : 'POST');
        $params['form_css'] = $this->getOption('form_css');

        return array_replace_recursive(parent::getTemplateParameters(), $params);
    }
}
