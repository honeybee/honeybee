<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Ui\Activity\ActivityInterface;
use Honeybee\Ui\Activity\Url;
use QL\UriTemplate\UriTemplate;

abstract class ActivityRenderer extends Renderer
{
    const STATIC_TRANSLATION_PATH = "activity";

    protected function validate()
    {
        $subject = $this->getPayload('subject');
        if (!$subject || ($subject && !$subject instanceof ActivityInterface)) {
            throw new RuntimeError(
                sprintf('Payload "subject" must be given and implement "%s".', ActivityInterface::CLASS)
            );
        }

        if ($subject->getType() === ActivityInterface::TYPE_WORKFLOW) {
            $important_payload_keys = [
                'resource',
                'module'
            ];
            $actual_payload_keys = $this->payload->getKeys();

            if (count(array_intersect($important_payload_keys, $actual_payload_keys)) === 0) {
                throw new RuntimeError(
                    sprintf(
                        'Payload of workflow activities must at least contain one of the following keys: %s',
                        implode(', ', $important_payload_keys)
                    )
                );
            }
        }

        $resource = $this->getPayload('resource');
        if ($resource && !$resource instanceof ProjectionInterface) {
            throw new RuntimeError('Payload "resource" must implement: ' . ProjectionInterface::CLASS);
        }

        $resource_type = $this->getPayload('module');
        if ($resource_type && !$resource_type instanceof ProjectionTypeInterface) {
            throw new RuntimeError('Payload "module" must implement: ' . ProjectionTypeInterface::CLASS);
        }
    }

    protected function doRender()
    {
        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
    }

    protected function getDefaultTemplateIdentifier()
    {
        if ($this->getPayload('subject')->getVerb() === 'write') {
            return $this->output_format->getName() . '/activity/form.twig';
        }

        return $this->output_format->getName() . '/activity/link.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $activity = $this->getPayload('subject');
        $params['activity'] = $activity->toArray();

        $activity_name = $activity->getName();
        if (empty($params['activity']['label'])) {
            $params['activity']['label'] = sprintf('%s.label', $activity_name);
        }
        if (empty($params['activity']['description'])) {
            $params['activity']['description'] = sprintf('%s.description', $activity_name);
        }

        $params['link'] = $this->getLinkFor($activity);

        if ($activity->getVerb() === 'write' && $this->hasPayload('resource')) {
            $revision = $this->getPayload('resource')->getRevision();
            $params['form_parameters'] = [ 'revision' => $revision ];
        }

        return $params;
    }

    protected function getLinkFor(ActivityInterface $activity)
    {
        $link = '';

        $parameters = (array)$this->getOption('additional_url_parameters', []);
        $options = (array)$this->getOption('additional_url_options', []);

        if ($this->hasPayload('resource')) {
            $parameters['resource'] = $this->getPayload('resource');
        } elseif ($this->hasPayload('module')) {
            $parameters['module'] = $this->getPayload('module');
        } else {
            // nothing special
        }

        return $this->genUrl($activity, $parameters, $options);
    }

    /**
     * Default translation domain for activities follows this fallback sequence:
     *  - 'view_scope' option
     *  - activity scope
     *  - application translation domain
     *
     * To override with a custom value pass to the renderer the 'translation_domain' option
     */
    protected function getDefaultTranslationDomain()
    {
        $view_scope = $this->getOption('view_scope');
        $activity_scope = $this->getPayload('subject')->getScopeKey();

        if (empty($view_scope)) {
            if (empty($activity_scope)) {
                $translation_domain_prefix = parent::getDefaultTranslationDomain();
            } else {
                // convention on scope value: the first 3 parts = vendor.package.resource_type
                $activity_scope_parts = explode('.', $activity_scope);
                $translation_domain_prefix = implode('.', array_slice($activity_scope_parts, 0, 3));
            }
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
