<?php

namespace Honeybee\Infrastructure\Command;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Success;
use Trellis\Common\Object;

class CommandBuilder implements CommandBuilderInterface
{
    protected $command_class;

    protected $command_state;

    public function __construct($command_class)
    {
        Assertion::classExists($command_class);

        $this->command_class = $command_class;
        $this->command_state = [];
    }

    public function build()
    {
        $result = $this->sanitizeCommandState($this->command_class, $this->command_state);

        if ($result instanceof Success) {
            return new $this->command_class($result->get());
        }

        throw new RuntimeError('Unable to build command, due to invalid command state.');
    }

    public function __call($method, array $args)
    {
        if (1 === preg_match('/^with(\w+)/', $method, $matches) && count($args) === 1) {
            $prop_name = StringToolkit::asSnakeCase($matches[1]);
            $this->command_state[$prop_name] = $args[0];
        }

        return $this;
    }

    protected function sanitizeCommandState($command_class, array $command_state)
    {
        $errors = [];
        $sanitized_state = [];

        foreach ($this->getCommandProperties($command_class) as $prop_name => $prop_info) {
            if ($prop_info['required']) {
                Assertion::keyExists($command_state, $prop_name);
            }
            if (isset($command_state[$prop_name])) {
                $prop_val = $command_state[$prop_name];
                $result = $this->adoptPropertyValue($prop_name, $prop_val);
                if ($result instanceof Success) {
                    $sanitized_state[$prop_name] = $result->get();
                } elseif ($result instanceof Error) {
                    $errors[] = array_merge($errors, $result->get());
                } else {
                    throw new RuntimeError('Invalid result type given. Either Success or Error expected.');
                }
            }
        }

        return empty($errors) ? new Success($sanitized_state) : new Error($errors);
    }

    protected function getCommandProperties($command_class)
    {
        $command_reflection = new ReflectionClass($command_class);
        $properties = [];

        foreach ($command_reflection->getProperties() as $property) {
            $doc_block = $property->getDocComment();
            $owning_class = $property->getDeclaringClass();
            $ignore_prop = $is_optional = preg_match('/@CommandBuilder::IGNORE\n/', $doc_block) === 1;
            $is_generic_object_prop = $owning_class->getName() === Object::CLASS;

            if (!$is_generic_object_prop && !$ignore_prop) {
                $properties[$property->getName()] = [
                    'name' => $property->getName(),
                    'required' =>  !preg_match('/@CommandBuilder::OPTIONAL\n/', $doc_block)
                ];
            }
        };

        return $properties;
    }

    protected function adoptPropertyValue($prop_name, $prop_value)
    {
        $validation_method = 'validate' . StringToolkit::asStudlyCaps($prop_name);
        $validation_callable = [ $this, $validation_method ];
        if (method_exists($this, $validation_method)) {
            return call_user_func($validation_callable, $prop_value);
        }

        return new Success($prop_value);
    }
}
