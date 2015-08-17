<?php

namespace Honeybee\Ui\Renderer;

use Trellis\Runtime\Attribute\AttributeInterface;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceLocatorInterface;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;
use Honeybee\Ui\Activity\ActivityInterface;
use Honeybee\Common\Util\StringToolkit;
use ReflectionClass;

class RendererService implements RendererServiceInterface
{
    protected $service_locator;
    protected $loaded_locators = [];
    protected $loaded_renderers = [];

    public function __construct(ServiceLocatorInterface $service_locator)
    {
        $this->service_locator = $service_locator;
    }

    public function renderSubject(
        $subject,
        OutputFormatInterface $output_format,
        ConfigInterface $renderer_config = null,
        array $additional_payload = [],
        SettingsInterface $render_settings = null
    ) {
        $renderer = $this->getRenderer($subject, $output_format, $renderer_config);
        $payload = array_merge([ 'subject' => $subject ], $additional_payload);
        return $renderer->render($payload, $render_settings);
    }

    public function getRenderer($subject, OutputFormatInterface $output_format, ConfigInterface $renderer_config = null)
    {
        if (null === $renderer_config) {
            $renderer_config = new ArrayConfig([]);
        }

        //return $this->createRenderer($subject, $output_format, $renderer_config);

        $cache_key = $this->buildCacheKeyFor($subject, $output_format, $renderer_config);

        if (!isset($this->loaded_renderers[$cache_key])) {
            $this->loaded_renderers[$cache_key] = $this->createRenderer($subject, $output_format, $renderer_config);
        }

        return $this->loaded_renderers[$cache_key];
    }

    public function createRenderer($subject, OutputFormatInterface $output_format, ConfigInterface $renderer_config = null)
    {
        $implementor = $this->determineImplementor($subject, $output_format, $renderer_config);

        $state = [
            ':config' => $renderer_config,
            ':output_format' => $output_format
        ];

        return $this->service_locator->createEntity($implementor, $state);
    }

    public function determineImplementor($subject, OutputFormatInterface $output_format, ConfigInterface $renderer_config = null)
    {
        $locator = $this->getRendererLocatorForOutputFormat($output_format);

        return $locator->locateRendererFor($subject, $renderer_config);
    }

    public function getRendererLocatorForOutputFormat(OutputFormatInterface $output_format)
    {
        $name = $output_format->getName();

        if (!array_key_exists($name, $this->loaded_locators)) {
            $implementor = $output_format->getRendererLocator();
            if (empty($implementor)) {
                $implementor = RendererLocator::CLASS;
            }
            if (empty($implementor)) {
                throw new RuntimeError('No renderer_locator implementor specified for output format: ' . $name);
            }

            $state = [
                ':output_format' => $output_format
            ];

            $this->loaded_locators[$name] = $this->service_locator->createEntity($implementor, $state);
        }

        return $this->loaded_locators[$name];
    }

    protected function buildCacheKeyFor($subject, OutputFormatInterface $output_format, ConfigInterface $renderer_config)
    {
        // @todo this has to become way more sophisticated, maybe we could introduce an interface
        // so that objects could provide their own portion to the cache-key: ICacheKeySource.getCacheKey
        return sprintf(
            '%s-%s-%s',
            $this->getSubjectName($subject),
            $output_format->getName(),
            $this->getHash($renderer_config->toArray())
        );
    }

    public function getTemplateRenderer()
    {
        $renderer = $this->service_locator->getTemplateRenderer();
        return $renderer;
    }

    protected function getHash($subject)
    {
        $string = '';
        if (is_null($subject)) {
            $string = 'null';
        } elseif (is_string($subject)) {
            $string = $subject;
        } elseif (is_array($subject)) {
            array_multisort($subject);
            $string = json_encode($subject);
        } else {
            $subject_name = gettype($subject);
            if (is_object($subject)) {
                $subject_class = new ReflectionClass($subject);
                $subject_name = $subject_class->getShortName();
                if ($subject instanceof ActivityInterface) {
                    $subject_name = $subject->getName() . $subject_name;
                }
            }
            $string = StringToolkit::asSnakeCase($subject_name);
        }

        return md5($string);
    }

    protected function getSubjectName($subject)
    {
        $name = '';
        if (is_null($subject)) {
            $name = 'null';
        } elseif (is_string($subject)) {
            $name = $subject;
        } else {
            $subject_name = gettype($subject);
            if (is_object($subject)) {
                $subject_class = new ReflectionClass($subject);
                $subject_name = $subject_class->getShortName();
                if ($subject instanceof ActivityInterface) {
                    $subject_name = $subject->getName() . $subject_name;
                }
            }
            $name = StringToolkit::asSnakeCase($subject_name);
        }

        return $name;
    }
}
