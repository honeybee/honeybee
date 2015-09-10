<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Ui\Activity\ActivityInterface;
use Honeybee\Ui\Activity\ActivityMap;

class ActivityMapRenderer extends Renderer
{
    const STATIC_TRANSLATION_PATH = "activity";

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

    /**
     * Default translation domain for activity maps follows this fallback sequence:
     *  - 'view_scope' option
     *  - application translation domain
     *
     * To override with a custom value pass to the renderer the 'translation_domain' option
     */
    protected function getDefaultTranslationDomain()
    {
        $view_scope = $this->getOption('view_scope');

        if (empty($view_scope)) {
            $translation_domain_prefix = parent::getDefaultTranslationDomain();
        } else {
            // convention on view_scope value: the first 3 parts = vendor.package.resource_type
            $view_scope_parts = explode('.', $view_scope);
            $translation_domain_prefix = implode('.', array_slice($view_scope_parts, 0, 3));
        }

        $translation_domain = sprintf(
            '%s.%s',
            $translation_domain_prefix,
            self::STATIC_TRANSLATION_PATH
        );

        return $translation_domain;
    }
}
