<?php

namespace Honeybee\Infrastructure\Command;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use ReflectionClass;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Result;
use Shrink0r\Monatic\Success;

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

    public function getCommandClass()
    {
        return $this->command_class;
    }

    /**
     * @return Result
     */
    public function build()
    {
        $result = $this->sanitizeCommandState($this->command_class, $this->command_state);

        if ($result instanceof Success) {
            $command = new $this->command_class($result->get());
            return Success::unit($command);
        }

        return $result;
    }

    /**
     * @return CommandBuilderInterface
     */
    public function __call($method, array $args)
    {
        if (1 === preg_match('/^with(\w+)/', $method, $matches) && count($args) === 1) {
            $property_name = StringToolkit::asSnakeCase($matches[1]);
            $this->command_state[$property_name] = $args[0];
        }

        return $this;
    }

    /**
     * @param string $command_class
     * @param mixed $command_state
     *
     * @return Result
     *
     * @throws RuntimeError
     */
    protected function sanitizeCommandState($command_class, array $command_state)
    {
        $errors = [];
        $sanitized_state = [];

        foreach ($this->getCommandProperties($command_class) as $property_name) {
            if (array_key_exists($property_name, $command_state)) {
                $property_value = $command_state[$property_name];
                $result = $this->adoptPropertyValue($property_name, $property_value);
                if ($result instanceof Success) {
                    $sanitized_state[$property_name] = $result->get();
                } elseif ($result instanceof Error) {
                    $errors[$property_name] = $result->get();
                } else {
                    throw new RuntimeError('Invalid result type given. Either Success or Error expected.');
                }
            }
        }

        return empty($errors) ? Success::unit($sanitized_state) : Error::unit($errors);
    }

    /**
     * @param string $command_class
     *
     * @return mixed[]
     */
    protected function getCommandProperties($command_class)
    {
        $command_reflection = new ReflectionClass($command_class);

        $properties = [];
        foreach ($command_reflection->getProperties() as $property) {
            $properties[] = $property->getName();
        }

        return $properties;
    }

    /**
     * @param string $prop_name
     * @param mixed $prop_value
     *
     * @return Result
     */
    protected function adoptPropertyValue($prop_name, $prop_value)
    {
        $validation_method = 'validate' . StringToolkit::asStudlyCaps($prop_name);
        if (method_exists($this, $validation_method)) {
            return call_user_func([ $this, $validation_method ], $prop_value);
        }

        return Success::unit($prop_value);
    }
}
