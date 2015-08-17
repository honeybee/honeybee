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
    const STATIC_TRANSLATION_PATH = "activities";

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

    protected function getDefaultTranslationDomain()
    {
        return sprintf(
            '%s.%s',
            parent::getDefaultTranslationDomain(),
            self::STATIC_TRANSLATION_PATH
        );
    }
}
