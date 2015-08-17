<?php

namespace Honeybee\Ui\Renderer\Console\Honeybee\Ui\Activity;

use Honeybee\Ui\Renderer\ActivityRenderer;

class ConsoleActivityRenderer extends ActivityRenderer
{
    public function doRender()
    {
        $activity = $this->getPayload('subject');

        $route_name = $activity->getUrl()->getValue();
        if ($activity->getVerb() === 'write') {
            $route_name .= '.write';
        }

        $parameters = $activity->getUrl()->getParameters();

        // add resource to route parameters as there's a routing callback that converts the ProjectionInterface
        // implementing resource to a valid URL query parameter called 'resource'
        if ($this->hasPayload('resource')) {
            $parameters['resource'] = $this->getPayload('resource');
        } elseif ($this->hasPayload('module')) {
            $parameters['module'] = $this->getPayload('module');
        }

        return $this->genUrl($route_name, $parameters);
    }
}

