<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;
use Psr\Log\LoggerInterface;

class RendererLocator implements RendererLocatorInterface
{
    const DEFAULT_LOOKUP_TEMPLATE = '{NAMESPACE}\\{OUTPUT_FORMAT_NAME}\\{SUBJECT}{MODIFIER}{SUFFIX}';
    const DEFAULT_LOOKUP_NAMESPACE = 'Honeybee\\Ui\\Renderer';
    const DEFAULT_LOOKUP_MODIFIER = '';
    const DEFAULT_LOOKUP_SUFFIX = 'Renderer';

    protected $logger;
    protected $output_format;
    protected $output_format_name;

    public function __construct(OutputFormatInterface $output_format, LoggerInterface $logger)
    {
        $this->output_format = $output_format;
        $this->output_format_name = StringToolkit::asStudlyCaps($output_format->getName());
        $this->logger = $logger;
    }

    /**
     * Tries to find renderer implementor given via config 'renderer' key and
     * then tries to find a renderer based on the type/class of the subject.
     *
     * @param mixed $subject subject to render
     * @param ConfigInterface $renderer_config configuration for the renderer when one is found
     *
     * @return string renderer implementor for the given subject (including namespace)
     */
    public function locateRendererFor($subject, ConfigInterface $renderer_config = null)
    {
        if (!empty($renderer_config) && $renderer_config->has('renderer')) {
            $implementor = $renderer_config->get('renderer', '');
            if (!empty($renderer_config) && $renderer_config->get('logging_enabled', false) === true) {
                $this->logger->debug(
                    sprintf(
                        '[%s] [OutputFormat=%s] [Subject=%s] Renderer set via renderer config: %s',
                        __METHOD__,
                        $this->output_format_name,
                        is_object($subject) ? get_class($subject) : gettype($subject),
                        $implementor
                    )
                );
            }
        } else {
            $implementor = $this->locateRendererImplementor($subject, $renderer_config);
        }

        if (!class_exists($implementor)) {
            throw new RuntimeError(
                sprintf(
                    'Determined "%s" renderer for subject "%s" not found: %s',
                    $this->output_format_name,
                    is_object($subject) ? get_class($subject) : gettype($subject),
                    $implementor
                )
            );
            // TODO add some hints about 'renderer' config key or specifying a locator in output_formats.xml?
        }

        return $implementor;
    }

    public function getOutputFormat()
    {
        return $this->output_format;
    }

    protected function locateRendererImplementor($subject, ConfigInterface $renderer_config = null)
    {
        $format_implementor = $this->getImplementorTemplate($renderer_config);

        $logging_enabled = false;
        if (!empty($renderer_config)) {
            $logging_enabled = $renderer_config->get('logging_enabled', false);
        }

        $implementor = '';
        if (!is_object($subject)) {
            $type = $this->buildTypeString(
                StringToolkit::asStudlyCaps(gettype($subject))
            );
            $implementor = str_replace('{SUBJECT}', $type, $format_implementor);
        } else {
            $type = get_class($subject); // includes namespaces
            $types = [ $type ];
            while (($type = get_parent_class($type)) !== false) {
                $types[] = $type;
            }

            $impls_tried = [];
            // try to get the most specific renderer for the subject's class
            foreach ($types as $type) {
                $impl = str_replace('{SUBJECT}', $this->buildTypeString($type), $format_implementor);
                if (class_exists($impl)) {
                    $implementor = $impl;
                    break;
                } else {
                    if ($logging_enabled) {
                        $this->logger->debug(
                            sprintf(
                                '[%s] [OutputFormat=%s] [Subject=%s] Renderer does not exist: %s',
                                __METHOD__,
                                $this->output_format_name,
                                is_object($subject) ? get_class($subject) : gettype($subject),
                                $impl
                            )
                        );
                    }
                }
                $impls_tried[] = $impl;
            }

            if (empty($implementor)) {
                throw new RuntimeError(
                    sprintf(
                        'No %s renderer for subject "%s" found. Renderers tried: %s',
                        $this->output_format_name,
                        is_object($subject) ? get_class($subject) : gettype($subject),
                        "\n- " . implode("\n- ", $impls_tried)
                    )
                );
            }
        }

        if ($logging_enabled) {
            $this->logger->debug(
                sprintf(
                    '[%s] [OutputFormat=%s] [Subject=%s] Renderer determined: %s',
                    __METHOD__,
                    $this->output_format_name,
                    is_object($subject) ? get_class($subject) : gettype($subject),
                    $implementor
                )
            );
        }

        return $implementor;
    }

    protected function getImplementorTemplate(ConfigInterface $renderer_config = null)
    {
        $implementor_template = $renderer_config->get('renderer_locator_lookup_template', self::DEFAULT_LOOKUP_TEMPLATE);
        $implementor_namespace = $renderer_config->get('renderer_locator_namespace', self::DEFAULT_LOOKUP_NAMESPACE);
        $implementor_modifier = $renderer_config->get('renderer_locator_modifier', self::DEFAULT_LOOKUP_MODIFIER);
        $implementor_suffix = $renderer_config->get('renderer_locator_suffix', self::DEFAULT_LOOKUP_SUFFIX);

        $implementor_template = str_replace('{NAMESPACE}', $implementor_namespace, $implementor_template);
        $implementor_template = str_replace('{OUTPUT_FORMAT_NAME}', StringToolkit::asStudlyCaps($this->output_format_name), $implementor_template);
        $implementor_template = str_replace('{MODIFIER}', StringToolkit::asStudlyCaps($implementor_modifier), $implementor_template);
        $implementor_template = str_replace('{SUFFIX}', StringToolkit::asStudlyCaps($implementor_suffix), $implementor_template);

        return $implementor_template;
    }

    protected function buildTypeString($namespaced_type)
    {
        $type_parts = explode('\\', $namespaced_type);
        $type_name = array_pop($type_parts);
        $type_parts[] = StringToolkit::asStudlyCaps($this->output_format_name) . $type_name;

        return implode('\\', $type_parts);
    }
}
