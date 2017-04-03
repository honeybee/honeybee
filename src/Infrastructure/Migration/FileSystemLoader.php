<?php

namespace Honeybee\Infrastructure\Migration;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\PhpClassParser;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\ServiceLocatorInterface;
use Symfony\Component\Finder\Finder;

class FileSystemLoader implements MigrationLoaderInterface
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
        $this->file_finder= $file_finder?: new Finder;
    }

    /**
     * @return MigrationList
     */
    public function loadMigrations()
    {
        $migration_dir = $this->config->get('directory');
        if (!is_dir($migration_dir) || !is_readable($migration_dir)) {
            throw new RuntimeError(sprintf('Given migration path is not a readable directory: %s', $migration_dir));
        }

        $migrations = [];
        $pattern = $this->config->get('pattern', '*.php');
        $migration_files = $this->file_finder->create()->files()->name($pattern)->in($migration_dir)->sortByName();

        foreach ($migration_files as $migration_file) {
            $class_parser = new PhpClassParser;
            $migration_class_info = $class_parser->parse((string)$migration_file);
            $migration_class = $migration_class_info->getFullyQualifiedClassName();
            $migration_class_name = $migration_class_info->getClassName();

            $class_format = $this->config->get('format', '#(?<version>\d{14}).(?<name>.+)$#');
            if (!preg_match($class_format, $migration_class_name, $matches)
                || !isset($matches['name'])
                || !isset($matches['version'])
            ) {
                throw new RuntimeError('Invalid class name format for ' . $migration_class_name);
            }

            if (!class_exists($migration_class)) {
                require_once $migration_class_info->getFilePath();
            }

            $migrations[] = $this->service_locator->make(
                $migration_class,
                [
                    ':state' => [
                        'name' => StringToolkit::asSnakeCase($matches['name']),
                        'version' => $matches['version']
                    ]
                ]
            );
        }

        return new MigrationList($migrations);
    }
}
