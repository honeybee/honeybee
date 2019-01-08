<?php

namespace Honeybee\Infrastructure\Job\Bundle;

use Closure;
use DateTimeImmutable;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\Job;
use Honeybee\Infrastructure\Job\Strategy\JobStrategy;

class ExecuteCommandJob extends Job
{
    const DATE_ISO8601_WITH_MICROS = 'Y-m-d\TH:i:s.uP';

    protected $command;

    /**
     * @hiddenProperty
     */
    protected $command_bus;

    /**
     * @hiddenProperty
     */
    protected $strategy;

    /**
     * @hiddenProperty
     */
    protected $strategy_callback;

    /**
     * @hiddenProperty
     */
    protected $settings;

    protected $iso_date;

    public function __construct(
        array $state,
        CommandBusInterface $command_bus,
        Closure $strategy_callback,
        SettingsInterface $settings = null
    ) {
        $this->iso_date = DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)))
            ->format(self::DATE_ISO8601_WITH_MICROS);

        parent::__construct($state);

        $this->command_bus = $command_bus;
        $this->strategy_callback = $strategy_callback;
        $this->settings = $settings ?: new Settings;
    }

    public function run(array $parameters = [])
    {
        $this->command_bus->execute($this->command);
    }

    public function getStrategy()
    {
        if (!$this->strategy) {
            $this->strategy = $this->createStrategy();
        }
        return $this->strategy;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getIsoDate()
    {
        return $this->iso_date;
    }

    protected function createStrategy()
    {
        $create_function = $this->strategy_callback;
        $strategy = $create_function($this);
        if (!$strategy instanceof JobStrategy) {
            throw new RuntimeError(
                sprintf(
                    'Invalid strategy type given: %s, expected instance of %s',
                    get_class($strategy),
                    JobStrategy::CLASS
                )
            );
        }
        return $strategy;
    }

    protected function setCommand($command_state)
    {
        if (is_array($command_state)) {
            if (!isset($command_state[self::OBJECT_TYPE])) {
                throw new RuntimeError('Unable to create command without type information.');
            }

            $command_implementor = $command_state[self::OBJECT_TYPE];
            if (!class_exists($command_implementor)) {
                throw new RuntimeError('Unable to resolve command implementor: ' . $command_implementor);
            }
            $this->command = new $command_implementor($command_state);
        } elseif ($command_state instanceof CommandInterface) {
            $this->command = $command_state;
        } else {
            throw new RuntimeError(
                sprintf(
                    'Invalid command data-type given. Only array and %s are supported.',
                    CommandInterface::CLASS
                )
            );
        }
    }
}
