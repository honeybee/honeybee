<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\ArrayToolkit;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Expression\ExpressionServiceInterface;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Ui\Activity\ActivityInterface;
use Honeybee\Ui\Activity\ActivityServiceInterface;
use Honeybee\Ui\Activity\Url;
use Honeybee\Ui\OutputFormat\OutputFormatInterface;
use Honeybee\Ui\OutputFormat\OutputFormatServiceInterface;
use Honeybee\Ui\Renderer\RendererServiceInterface;
use Honeybee\Ui\TranslatorInterface;
use Honeybee\Ui\UrlGeneratorInterface;
use Honeybee\Ui\ViewConfig\NameResolverInterface;
use Honeybee\Ui\ViewConfig\ViewConfigServiceInterface;
use Honeybee\Ui\ViewTemplate\ViewTemplateServiceInterface;
use Psr\Log\LoggerInterface;
use QL\UriTemplate\UriTemplate;

abstract class Renderer implements RendererInterface
{
    protected $renderer_service;
    protected $output_format;
    protected $config;
    protected $template_renderer;
    protected $url_generator;
    protected $translator;
    protected $output_format_service;
    protected $view_config_service;
    protected $view_template_service;
    protected $activity_service;
    protected $expression_service;
    protected $name_resolver;
    protected $resource_type_map;
    protected $logger;
    protected $payload;
    protected $settings;

    abstract protected function validate();
    abstract protected function doRender();

    public function __construct(
        RendererServiceInterface $renderer_service,
        OutputFormatInterface $output_format,
        ConfigInterface $config,
        TemplateRendererInterface $template_renderer,
        UrlGeneratorInterface $url_generator,
        TranslatorInterface $translator,
        OutputFormatServiceInterface $output_format_service,
        ViewConfigServiceInterface $view_config_service,
        ViewTemplateServiceInterface $view_template_service,
        ActivityServiceInterface $activity_service,
        ExpressionServiceInterface $expression_service,
        NameResolverInterface $name_resolver,
        ProjectionTypeMap $resource_type_map,
        LoggerInterface $logger
    ) {
        $this->renderer_service = $renderer_service;
        $this->output_format = $output_format;
        $this->config = $config ?: new ArrayConfig([]);
        $this->template_renderer = $template_renderer;
        $this->url_generator = $url_generator;
        $this->translator = $translator;
        $this->output_format_service = $output_format_service;
        $this->view_config_service = $view_config_service;
        $this->view_template_service = $view_template_service;
        $this->activity_service = $activity_service;
        $this->expression_service = $expression_service;
        $this->name_resolver = $name_resolver;
        $this->resource_type_map = $resource_type_map;
        $this->logger = $logger;
    }

    public function render($payload, $settings = null)
    {
        $this->setUp($payload, $settings);

        $this->validate();

        $output = $this->doRender();

        $this->tearDown();

        return $output;
    }

    protected function setUp($payload, $settings)
    {
        if (null === $payload) {
            $payload = new Settings();
        } elseif (is_array($payload)) {
            $payload = new Settings($payload);
        } elseif ($payload instanceof SettingsInterface) {
            $payload = new Settings($payload->toArray());
        }

        $this->payload = $payload;

        if (null === $settings) {
            $settings = new Settings();
        } elseif (is_array($settings)) {
            $settings = new Settings($settings);
        } elseif (!$settings instanceof SettingsInterface) {
            throw new RuntimeError('Settings must be an array or SettingsInterface implementing instance.');
        }

        $this->settings = $settings;
    }

    protected function tearDown()
    {
        $this->payload = null;
        $this->settings = null;
    }

    protected function getTemplateIdentifier($default = null)
    {
        if (null === $default) {
            $default = $this->getDefaultTemplateIdentifier();
        }

        return $this->getOption('template', $default);
    }

    protected function getDefaultTemplateIdentifier()
    {
        return 'attribute/empty.twig';
    }

    protected function getTemplateParameters()
    {
        return $this->getDefaultTemplateParameters();
    }

    protected function getDefaultTemplateParameters()
    {
        // $tm = $this->getTranslationManager();
        // $locale = $tm->getCurrentLocale();
        $translation_domain = $this->getTranslationDomain();
        return [
            'output_format_name' => $this->output_format->getName(),
            'translation_domain' => $translation_domain,
            'translation_domain_panels' => $this->getOption(
                'translation_domain_panels',
                sprintf('%s.panels', $translation_domain)
            ),
            'translation_domain_tabs' => $this->getOption(
                'translation_domain_tabs',
                sprintf('%s.tabs', $translation_domain)
            ),
            'translation_domain_groups' => $this->getOption(
                'translation_domain_groups',
                sprintf('%s.groups', $translation_domain)
            ),
            // 'locale' => $tm->getCurrentLocaleIdentifier(),
            // 'locale_default' => $tm->getDefaultLocaleIdentifier(),
            // 'locale_language' => $locale->getLocaleLanguage(),
            // 'locale_territory' => $locale->getLocaleTerritory(),
            // 'locale_currency' => $locale->getLocaleCurrency(),
            'options' => $this->getOptions(),
            'translations' => $this->getTranslations($translation_domain)
        ];
    }

    protected function getTranslationDomain($default = '')
    {
        if (empty($default)) {
            $default = $this->getDefaultTranslationDomain();
        }

        return $this->getOption('translation_domain', $default);
    }

    protected function getDefaultTranslationDomain()
    {
        return 'application';
    }

    protected function getTemplateRenderer()
    {
        if ($this->hasOption('template_renderer')) {
            return $this->getOption('template_renderer');
        }

        return $this->template_renderer;
    }

    // @codingStandardsIgnoreStart
    protected function _($text, $domain = null, $locale = null, array $params = null, $fallback = null)
    {
        // @codingStandardsIgnoreEnd
        return $this->translator->translate($text, $this->getTranslationDomain($domain), $locale, $params, $fallback);
    }

    /**
     * Return an array of already translated keys to eventually use in the template.
     *
     * These keys correspond to a list of default keys for common translations, and optional
     * additional keys that can be defined in the 'translation_keys' array setting of the renderer.
     *
     * @param string $translation_domain for a custom domain
     *
     * @return array Translated strings to use in the template
     */
    protected function getTranslations($translation_domain = null)
    {
        $translations = [];
        $translation_keys = $this->getTranslationKeys($translation_domain);

        foreach ($translation_keys as $key => $key_value) {
            $translation = $this->_($key_value, $translation_domain);
            // add just keys with corresponding translations
            if ($translation !== $key) {
                $translations[$key] = $this->_($key_value, $translation_domain);
            }
        }

        return $translations;
    }

    protected function getTranslationKeys()
    {
        // remove eventual duplicates and set default value the same as the key, for default translation keys
        $default_translation_keys = array_unique($this->getDefaultTranslationKeys());
        $default_translation_keys = array_combine($default_translation_keys, $default_translation_keys);

        $translation_keys = (array)$this->getOption('translation_keys', new Settings([]));

        return ArrayToolkit::mergeScalarSafe($default_translation_keys, $translation_keys);
    }

    protected function getDefaultTranslationKeys()
    {
        return [];
    }

    protected function genUrl($name, array $parameters = [], array $options = [])
    {
        $url = '';
        if ($name instanceof ActivityInterface) {
            $url = $name->getUrl();
        } elseif ($name instanceof Url) {
            $url = $name;
        } else {
            $url = Url::createRoute($name, $parameters);
        }

        if ($url->getType() === Url::TYPE_ROUTE) {
            $route_params = $parameters;
            $route_params = array_replace_recursive($url->getParameters(), $parameters);
            $link = $this->url_generator->generateUrl($url->getValue(), $route_params, $options);
        } elseif ($url->getType() === Url::TYPE_URI_TEMPLATE) {
            $uri_template = new UriTemplate($url->getValue());
            $template_params = array_replace_recursive($url->getParameters(), $parameters);
            $link = $uri_template->expand($template_params);
        } else {
            $link = $url->__toString(); // TODO apply params as query params?
        }

        return $link;
    }

    protected function getPayload($name, $default = null)
    {
        return $this->payload->get($name, $default);
    }

    protected function hasPayload($name)
    {
        return $this->payload->has($name);
    }

    /**
     * Returns the value for the given setting or configuration entry. Config
     * value is only returned when settings value does not exist.
     *
     * @param string $name key from renderer config or render settings
     * @param mixed $default_value default value to return if neither setting nor config entry exists
     *
     * @return mixed setting value or config value or default value given
     */
    protected function getOption($name, $default_value = null)
    {
        if ($this->settings->has($name)) {
            return $this->settings->get($name);
        } elseif ($this->config->has($name)) {
            return $this->config->get($name);
        }

        return $default_value;
    }

    /**
     * Tells whether there's a key in renderer config or render settings with that name.
     *
     * @param string $name key from renderer config or render settings
     *
     * @return boolean true if key exists in either config or settings
     */
    protected function hasOption($name)
    {
        return ( $this->settings->has($name) || $this->config->has($name) );
    }

    /**
     * @return array of render settings merged over renderer config
     */
    protected function getOptions()
    {
        $config = $this->config->toArray();
        $settings = $this->settings->toArray();
        return ArrayToolkit::mergeScalarSafe($config, $settings);
    }
}
