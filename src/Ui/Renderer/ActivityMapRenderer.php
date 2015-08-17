<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Ui\Activity\ActivityInterface;
use Honeybee\Ui\Activity\ActivityMap;

class ActivityMapRenderer extends Renderer
{
    const STATIC_TRANSLATION_PATH = "activities";

    protected function validate()
    {
        $activity_map = $this->getPayload('subject');
        if (!$activity_map instanceof ActivityMap) {
            throw new RuntimeError('Payload "subject" must be an instance of: ' . ActivityMap::CLASS);
        }

        if (count($activity_map->filterByType(ActivityInterface::TYPE_WORKFLOW)) > 0) {
            if (!$this->hasPayload('resource')) {
                throw new RuntimeError(
                    'Payload "resource" missing from ActivityMap that contains workflow activities. ' .
                    'The resource must implement ProjectionInterface to be used to generate correct URLs.'
                );
            }
            $resource = $this->getPayload('resource');
            if ($resource && !$resource instanceof ProjectionInterface) {
                throw new RuntimeError('Payload "resource" must be an instance of: ' . ProjectionInterface::CLASS);
            }
        }
    }

    protected function doRender()
    {
        $activity_map = $this->getPayload('subject');

        if ($activity_map->isEmpty()) {
            return '';
        }

        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
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
