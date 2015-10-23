<?php

namespace Honeybee\Model\Command;

use Honeybee\Infrastructure\Command\CommandBuilder;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Success;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Validator\Result\IncidentInterface;

class AggregateRootCommandBuilder extends CommandBuilder
{
    protected $aggregate_root_type;

    protected $embedded_builders;

    public function __construct(AggregateRootTypeInterface $aggregate_root_type, $command_class)
    {
        parent::__construct($command_class);

        $this->aggregate_root_type = $aggregate_root_type;
        $this->command_state['aggregate_root_type'] = get_class($aggregate_root_type);
        $this->embedded_builders = [];
    }

    /**
     * @return Result
     */
    protected function validateValues(array $values)
    {
        $errors = [];
        $sanitized_values = [];

        foreach ($this->aggregate_root_type->getAttributes() as $attribute_name => $attribute) {
            $attribute = $this->aggregate_root_type->getAttribute($attribute_name);
            if (isset($values[$attribute_name])) {
                $result = $this->sanitizeAttributeValue($attribute, $values[$attribute_name]);
                if ($result instanceof Success) {
                    $sanitized_values[$attribute_name] = $result->get();
                } else {
                    $errors[] = $result->get();
                }
            }
        }

        return empty($errors) ? Success::unit($sanitized_values) : Error::unit($errors);
    }

    /**
     * @return Result
     */
    protected function sanitizeAttributeValue(AttributeInterface $attribute, $value)
    {
        $errors = [];
        $sanitized_value = null;

        $value_holder = $attribute->createValueHolder();
        $result = $value_holder->setValue($value);

        if ($result->getSeverity() > IncidentInterface::NOTICE) {
            $success = false;
            foreach ($result->getViolatedRules() as $rule) {
                foreach ($rule->getIncidents() as $name => $incident) {
                    $error_key = $attribute->getPath() . '.' . $name;
                    $incident_params = $incident->getParameters();
                    $errors[] = [ $attribute->getName(), $error_key, $incident_params ];
                }
            }
        }

        return empty($errors) ? Success::unit($value_holder->toNative()) : Error::unit($errors);
    }
}
