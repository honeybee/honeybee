<?php

namespace Honeybee\Infrastructure\Migration;

use Auryn\Injector;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\PhpClassParser;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;

class FileSystemLoader implements MigrationLoaderInterface
{
    const GLOB_EXPRESSION = '*.php';

    protected $config;

    protected $injector;

    public function __construct(ConfigInterface $config, Injector $injector)
    {
        $this->config = $config;
        $this->injector = $injector;
    }

    /**
     * @return MigrationList
     */
    public function loadMigrations()
    {
        $migration_dir = $this->config->get('directory');
        if (!is_dir($migration_dir)) {
            throw new RuntimeError(sprintf('Given migration path is not a directory: %s', $migration_dir));
        }

        $migration_list = new MigrationList;
        $glob_expression = sprintf(
            '%1$s%2$s[0-9]*%2$s%3$s',
            $migration_dir,
            DIRECTORY_SEPARATOR,
            self::GLOB_EXPRESSION
        );

        foreach (glob($glob_expression) as $migration_file) {
            $class_parser = new PhpClassParser;
            $migration_class_info = $class_parser->parse($migration_file);
            $migration_class = $migration_class_info->getFullyQualifiedClassName();

            if (!class_exists($migration_class)) {
                require_once $migration_class_info->getFilePath();
            }

            if (!class_exists($migration_class)) {
                throw new RuntimeError(
                    sprintf("Unable to load migration class %s", $migration_class)
                );
            }

            $class_name_parts = explode('_', $migration_class_info->getClassName());
            $name = $class_name_parts[2] . (isset($class_name_parts[3]) ? $class_name_parts[3] : '');
            $version = $class_name_parts[1];
            $migration = $this->injector->make(
                $migration_class,
                [
                    ':state' => [
                        'name' => StringToolkit::asSnakeCase($name),
                        'version' => $version
                    ]
                ]
            );
            $migration_list->addItem($migration);
        }

        return $migration_list;
    }
}
