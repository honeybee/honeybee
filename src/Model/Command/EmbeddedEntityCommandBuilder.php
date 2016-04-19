<?php

namespace Honeybee\Model\Command;

use Honeybee\EntityTypeInterface;
use Honeybee\Infrastructure\Command\CommandBuilder;
use Honeybee\Infrastructure\Command\CommandBuilderList;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Success;
use Shrink0r\Monatic\Result;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Validator\Result\IncidentInterface;

class EmbeddedEntityCommandBuilder extends CommandBuilder
{
    protected $entity_type;

    public function __construct(EntityTypeInterface $entity_type, $command_class)
    {
        parent::__construct($command_class);

        $this->entity_type = $entity_type;
        $this->command_state['embedded_entity_type'] = $entity_type->getPrefix();
        $this->command_state['embedded_entity_commands'] = new CommandBuilderList();
    }

    /**
     * Sort properties in order to handle embedded entity commands last
     *
     * @return array
     */
    protected function getCommandProperties($command_class)
    {
        $properties = parent::getCommandProperties($command_class);
        usort(
            $properties,
            function ($a, $b) {
                return $b == 'embedded_entity_commands' ? -1 : 1;
            }
        );
        return $properties;
    }

    public function getParentAttributeName()
    {
        return $this->command_state['parent_attribute_name'];
    }

    /**
     * @return Result
     */
    protected function validateValues(array $values)
    {
        $errors = [];
        $sanitized_values = [];

        foreach ($this->entity_type->getAttributes() as $attribute_name => $attribute) {
            if (isset($values[$attribute_name])) {
                if ($attribute instanceof EmbeddedEntityListAttribute) {
                    // create embedded commands
                    foreach ($values[$attribute_name] as $pos => $embedded_value) {
                        $type_prefix = $embedded_value['@type'];
                        unset($embedded_value['@type']);
                        $this->command_state['embedded_entity_commands']->addItem(
                            (new self(
                                $attribute->getEmbeddedTypeByPrefix($type_prefix),
                                AddEmbeddedEntityCommand::CLASS
                            ))
                            ->withParentAttributeName($attribute_name)
                            ->withPosition($pos)
                            ->withValues($embedded_value)
                        );
                    }
                    continue;
                } else {
                    $result = $this->sanitizeAttributeValue($attribute, $values[$attribute_name]);
                }

                if ($result instanceof Success) {
                    $sanitized_values[$attribute_name] = $result->get();
                } elseif ($result instanceof Error) {
                    $errors[] = $result->get();
                }
            }
        }

        return empty($errors) ? Success::unit($sanitized_values) : Error::unit($errors);
    }

    /**
     * @return Result
     */
    protected function validateEmbeddedEntityCommands(CommandBuilderList $builder_list)
    {
        $commands = [];
        $errors = [];
        foreach ($builder_list as $pos => $command_builder) {
            $result = $command_builder->build();
            if ($result instanceof Success) {
                $commands[] = $result->get();
            } elseif ($result instanceof Error) {
                $errors[$command_builder->getParentAttributeName()][] = $result->get();
            }
        }

        return empty($errors) ? Success::unit($commands) : Error::unit($errors);
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
            foreach ($result->getViolatedRules() as $rule) {
                foreach ($rule->getIncidents() as $name => $incident) {
                    $incident_params = $incident->getParameters();
                    $errors[$attribute->getName()]['@incidents'][] = [ $name => $incident_params ];
                }
            }
        }

        return empty($errors) ? Success::unit($value_holder->toNative()) : Error::unit($errors);
    }
}
