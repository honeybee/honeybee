<?php

namespace Honeybee\Model\Command;

use Honeybee\Infrastructure\Command\CommandBuilder;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Success;
use Trellis\Runtime\Validator\Result\IncidentInterface;

class AggregateRootCommandBuilder extends CommandBuilder
{
    protected $aggregate_root_type;

    public function __construct(AggregateRootTypeInterface $aggregate_root_type, $command_class)
    {
        parent::__construct($command_class);

        $this->aggregate_root_type = $aggregate_root_type;
        $this->command_state['aggregate_root_type'] = get_class($aggregate_root_type);
    }

    protected function validateValues(array $values)
    {
        $errors = [];
        $sanitized_values = [];

        foreach ($this->aggregate_root_type->getAttributes() as $attribute_name => $attribute) {
            $attribute = $this->aggregate_root_type->getAttribute($attribute_name);
            if (isset($values[$attribute_name])) {
                $value_holder = $attribute->createValueHolder();
                $result = $value_holder->setValue($values[$attribute_name]);
                if ($result->getSeverity() > IncidentInterface::NOTICE) {
                    $success = false;
                    foreach ($result->getViolatedRules() as $rule) {
                        foreach ($rule->getIncidents() as $name => $incident) {
                            $error_key = $attribute->getPath() . '.' . $name;
                            $incident_params = $incident->getParameters();
                            $errors[$attribute_name] = [ $error_key, $incident_params ];
                        }
                    }
                }
                $sanitized_values[$attribute_name] = $value_holder->toNative();
            }
        }

        return empty($errors) ? new Success($sanitized_values) : new Error($errors);
    }
}
