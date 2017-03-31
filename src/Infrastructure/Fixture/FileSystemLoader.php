<?php

namespace Honeybee\Infrastructure\Fixture;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\PhpClassParser;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\ServiceLocatorInterface;
use Symfony\Component\Finder\Finder;

class FileSystemLoader implements FixtureLoaderInterface
{
    protected $config;

    protected $service_locator;

    protected $file_finder;

    public function __construct(
        ConfigInterface $config,
        ServiceLocatorInterface $service_locator,
        Finder $file_finder = null
    ) {
        $this->config = $config;
        $this->service_locator = $service_locator;
        $this->file_finder = $file_finder?: new Finder;
    }

    /**
     * @return FixtureList
     */
    public function loadFixtures()
    {
        $fixture_dir = $this->config->get('directory');
        if (!is_dir($fixture_dir) || !is_readable($fixture_dir)) {
            throw new RuntimeError(sprintf('Given fixture path is not a readable directory: %s', $fixture_dir));
        }

        $fixtures = [];
        $pattern = $this->config->get('pattern', '*.php');
        $fixture_files = $this->file_finder->create()->files()->name($pattern)->in($fixture_dir);

        foreach ($fixture_files as $fixture_file) {
            $class_parser = new PhpClassParser;
            $fixture_class_info = $class_parser->parse((string)$fixture_file);
            $fixture_class = $fixture_class_info->getFullyQualifiedClassName();
            $fixture_class_name = $fixture_class_info->getClassName();

            $class_format = $this->config->get('format', '#(?<version>\d{14}).(?<name>.+)$#');
            if (!preg_match($class_format, $fixture_class_name, $matches)
                || !isset($matches['name'])
                || !isset($matches['version'])
            ) {
                throw new RuntimeError('Invalid class name format for ' . $fixture_class_name);
            }

            if (!class_exists($fixture_class)) {
                require_once $fixture_class_info->getFilePath();
            }

            $fixtures[] = $this->service_locator->make(
                $fixture_class,
                [
                    ':state' => [
                        'name' => StringToolkit::asSnakeCase($matches['name']),
                        'version' => $matches['version']
                    ]
                ]
            );
        }

        return new FixtureList($fixtures);
    }
}
