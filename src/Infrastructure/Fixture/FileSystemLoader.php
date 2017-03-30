<?php

namespace Honeybee\Infrastructure\Fixture;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\PhpClassParser;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\ServiceLocatorInterface;

class FileSystemLoader implements FixtureLoaderInterface
{
    const GLOB_EXPRESSION = '*.php';

    protected $config;

    protected $service_locator;

    public function __construct(ConfigInterface $config, ServiceLocatorInterface $service_locator)
    {
        $this->config = $config;
        $this->service_locator = $service_locator;
    }

    /**
     * @return FixtureList
     */
    public function loadFixtures()
    {
        $fixture_dir = $this->config->get('directory');
        if (!is_dir($fixture_dir)) {
            throw new RuntimeError(sprintf('Given fixture path is not a directory: %s', $fixture_dir));
        }

        $fixture_list = new FixtureList;
        $glob_expression = sprintf(
            '%1$s%2$s[0-9]*%2$s%3$s',
            $fixture_dir,
            DIRECTORY_SEPARATOR,
            self::GLOB_EXPRESSION
        );

        foreach (glob($glob_expression) as $fixture_file) {
            $class_parser = new PhpClassParser;
            $fixture_class_info = $class_parser->parse($fixture_file);
            $fixture_class = $fixture_class_info->getFullyQualifiedClassName();

            if (!class_exists($fixture_class)) {
                require_once $fixture_class_info->getFilePath();
            }

            if (!class_exists($fixture_class)) {
                throw new RuntimeError(
                    sprintf("Unable to load fixture class %s", $fixture_class)
                );
            }

            $class_name_parts = explode('_', $fixture_class_info->getClassName());
            $fixture = $this->service_locator->make(
                $fixture_class,
                [
                    ':state' => [
                        'name' => StringToolkit::asSnakeCase($class_name_parts[2]),
                        'version' => $class_name_parts[1]
                    ]
                ]
            );

            $fixture_list->addItem($fixture);
        }

        return $fixture_list;
    }
}
