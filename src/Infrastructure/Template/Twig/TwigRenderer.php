<?php

namespace Honeybee\Infrastructure\Template\Twig;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\Infrastructure\Template\Twig\Extension\ToolkitExtension;
use Honeybee\Infrastructure\Template\Twig\Loader\FilesystemLoader;
use Symfony\Component\Filesystem\Filesystem;
use Twig_Environment;
use Twig_Loader_String;

class TwigRenderer implements TemplateRendererInterface
{
    protected $twig;
    protected $filesystem;

    protected static $default_settings = [
        'twig_options' => [
            'autoescape' => true,
            'cache' => false,
            'debug' => false,
            'strict_variables' => true,
        ],
        'twig_extensions' => [
            ToolkitExtension::CLASS,
        ],
        'template_paths' => [],
    ];

    /**
     * @param Twig_Environment $twig configured twig instance
     * @param Filesystem $filesystem filesystem instance with dumpFile() method
     */
    public function __construct(Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    /**
     * Renders the given template using the given variables and returns the rendered result.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process; IGNORED for twig
     *
     * @return mixed result of the rendered template
     */
    public function render($template, array $data = [], array $settings = [])
    {
        return $this->twig->render($template, $data);
    }

    /**
     * Renders the wanted template using the given variables as context into the specified target location.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param string $target_file local filesystem target path location (including file name)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process; IGNORED for twig
     *
     * @throws Symfony\Component\Filesystem\Exception\IOException if target file cannot be written
     */
    public function renderToFile($template, $target_file, array $data = [], array $settings = [])
    {
        $content = $this->twig->render($template, $data, $settings);
        $this->filesystem->dumpFile($target_file, $content);
    }

    /**
     * Renders the given template source code string using the given data and returns the rendered string.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process; IGNORED for twig
     *
     * @return string result of the rendered template source
     */
    public function renderToString($template, array $data = [], array $settings = [])
    {
        $original_loader = $this->twig->getLoader();
        $this->twig->setLoader(new Twig_Loader_String());
        $content = $this->twig->render($template, $data, $settings);
        $this->twig->setLoader($original_loader);

        return $content;
    }

    /**
     * Available settings:
     *
     * - 'twig_options'                 array of twig environment options that will be merged with the default options
     * - 'twig_extensions'              array of instances or fully qualified class names for twig extensions to add
     * - 'template_paths'               array of locations to lookup templates in
     * - 'allowed_template_extensions'  array of allowed template filename extensions
     * - 'cache_scope'                  string to use for twig loader cache key generation
     *
     * Default twig environment options used are:
     *
     * - 'autoescape': true
     * - 'cache': false
     * - 'debug': false
     * - 'strict_variables: true
     *
     * Default twig extension added: Honeybee\Infrastructure\Template\Twig\Extensions\ToolkitExtension
     *
     * @param array $settings configuration for the renderer and its default twig instance
     */
    public static function create(array $settings = [])
    {
        $settings = new Settings(array_replace_recursive(static::$default_settings, $settings));

        return new static(static::createTwigRenderer($settings), new Filesystem());
    }

    protected static function createTwigRenderer(Settings $settings)
    {
        $loader = static::createTwigLoader($settings);

        $twig_options = (array)$settings->get('twig_options', []);
        $twig = new Twig_Environment($loader, $twig_options);

        $twig_extensions = (array)$settings->get('twig_extensions', []);
        foreach ($twig_extensions as $extension_class) {
            if (is_object($extension_class)) {
                $twig->addExtension($extension_class);
            } else {
                $twig->addExtension(new $extension_class());
            }
        }

        return $twig;
    }

    protected static function createTwigLoader(Settings $settings)
    {
        if (!$settings->has('template_paths')) {
            throw new RuntimeError('Missing "template_paths" settings with template lookup locations.');
        }

        $template_paths = (array)$settings->get('template_paths', []);

        $loader = new FilesystemLoader($template_paths);
        if ($settings->has('allowed_template_extensions')) {
            $loader->setAllowedExtensions((array)$settings->get('allowed_template_extensions'));
        }

        if (!$settings->has('cache_scope')) {
            $loader->setScope(spl_object_hash($loader)); // unique scope for each new loader instance
        } else {
            $loader->setScope($settings->get('cache_scope', FilesystemLoader::SCOPE_DEFAULT));
        }

        return $loader;
    }
}
