<?php

namespace Honeybee\Model\Command;

use Honeybee\EntityInterface;
use Honeybee\EntityTypeInterface;
use Honeybee\Infrastructure\Command\CommandBuilder;
use Honeybee\Infrastructure\Command\CommandBuilderList;
use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Success;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\EntityList\EntityList;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;

class EmbeddedEntityCommandBuilder extends CommandBuilder
{
    protected $entity_type;

    public function __construct(EntityTypeInterface $entity_type, $command_class)
    {
        parent::__construct($command_class);

        $this->entity_type = $entity_type;
        $this->command_state['embedded_entity_type'] = $entity_type->getPrefix();
        $this->command_state['embedded_entity_commands'] = new EmbeddedEntityTypeCommandList;
    }

    /**
     * @return array
     */
    protected function getCommandProperties($command_class)
    {
        $properties = parent::getCommandProperties($command_class);

        // sort properties to ensure embedded entity commands are validated last
        usort(
            $properties,
            function ($a, $b) {
                return $b === 'embedded_entity_commands' ? -1 : 1;
            }
        );

        return $properties;
    }

    public function getParentAttributeName()
    {
        return $this->command_state['parent_attribute_name'];
    }

    /**
     * @param EntityListAttribute $attribute
     * @param mixed[] $values
     *
     * @return \Shrink0r\Monatic\Result
     */
    protected function getEmbeddedCommands(EntityListAttribute $attribute, array $values)
    {
        $errors = [];
        $affected_identifiers = [];
        $attribute_name = $attribute->getName();
        $embedded_entity_list = $this->entity ? $this->entity->get($attribute_name) : new EntityList;
        $builder_list = new CommandBuilderList;

        foreach ($values as $position => $embedded_values) {
            if (!isset($embedded_values['@type'])) {
                $value_path = sprintf('%s.%d.@type', $attribute_name, $position);
                $errors[$value_path]['@incidents'][] = [
                    'path' => $attribute->toTypePath(),
                    'incidents' => [ 'invalid_type' => [ 'reason' => 'missing' ] ]
                ];
                continue;
            }

            $embed_type_prefix = $embedded_values['@type'];
            unset($embedded_values['@type']);
            $embed_type = $attribute->getEmbeddedTypeByPrefix($embed_type_prefix);

            if (!$embed_type) {
                $value_path = sprintf('%s.%d.@type', $attribute_name, $position);
                $errors[$value_path]['@incidents'][] = [
                    'path' => $attribute->getPath(),
                    'incidents' => [ 'invalid_type' => [ 'reason' => 'unknown' ] ]
                ];
                continue;
            }

            /*
             * Filter entities from the entity by incoming payload identifiers. If the
             * identifier is not matched then prepare an 'add' command, otherwise a 'modify'.
             */
            $affected_entity = $embedded_entity_list->filter(
                function ($embedded_entity) use ($embedded_values) {
                    return $embedded_entity instanceof EntityInterface
                    && isset($embedded_values['identifier'])
                    && $embedded_entity->getIdentifier() === $embedded_values['identifier'];
                }
            )->getFirst();

            if (!$affected_entity) {
                $builder_list->push(
                    (new self($embed_type, AddEmbeddedEntityCommand::CLASS))
                    ->withParentAttributeName($attribute_name)
                    ->withPosition($position)
                    ->withValues($embedded_values)
                );
            } else {
                $affected_identifiers[] = $affected_entity->getIdentifier();
                $modified_values = $this->filterUnmodifiedValues($affected_entity, $embedded_values);
                // prepare a modify command if values or position has changed
                if (!empty($modified_values)
                    || $embedded_entity_list->getKey($affected_entity) != $position
                ) {
                    $builder_list->push(
                        (new self($embed_type, ModifyEmbeddedEntityCommand::CLASS))
                        ->withParentAttributeName($attribute_name)
                        ->withEmbeddedEntityIdentifier($affected_entity->getIdentifier())
                        ->withPosition($position)
                        ->withValues($modified_values)
                    );
                }
            }
        }

        /*
         * Iterate the attribute entity list and prepare remove commands for
         * any embedded entities with no incoming payload, or compensate for commands
         * which already exist.
         */
        foreach ($embedded_entity_list as $embedded_entity) {
            // if an entity is not found in the payload then we do compensation checks to make
            // sure we don't inadvertently add an existing entity
            if (!in_array($embedded_entity->getIdentifier(), $affected_identifiers)) {
                // look for any add command which has no difference in values for the current entity
                $command_key = null;
                foreach ($builder_list as $key => $command_builder) {
                    if ($command_builder->getCommandClass() === AddEmbeddedEntityCommand::CLASS
                        && empty($this->filterUnmodifiedValues($embedded_entity, $command_builder->getValues()))
                    ) {
                        $command_key = $key;
                        break;
                    }
                }
                if (!is_null($command_key)) {
                    // remove the unnecessary add command if the entity already exists in the entity
                    $builder_list->splice($command_key);
                } else {
                    // the entity was not found in the payload so we can prepare removal
                    $builder_list->push(
                        (new self($embedded_entity->getType(), RemoveEmbeddedEntityCommand::CLASS))
                        ->withParentAttributeName($attribute_name)
                        ->withEmbeddedEntityIdentifier($embedded_entity->getIdentifier())
                    );
                }
            }
        }

        $build_result = $builder_list->build();

        if (!empty($errors)) {
            if ($build_result instanceof Error) {
                $errors = array_merge($errors, $build_result->get());
            }
            return Error::unit($errors);
        }

        return $build_result;
    }

    /**
     * @return mixed[]
     */
    public function getValues()
    {
        return isset($this->command_state['values']) ? $this->command_state['values'] : [];
    }

    /**
     * @param mixed[] $values
     *
     * @return \Shrink0r\Monatic\Result
     */
    protected function validateValues(array $values)
    {
        $errors = [];
        $sanitized_values = [];

        /* @var \Trellis\EntityType\Attribute\AttributeInterface $attribute */
        foreach ($this->entity_type->getAttributes() as $attribute_name => $attribute) {
            if (isset($values[$attribute_name])) {
                if ($attribute instanceof EntityListAttribute) {
                    /*
                     * prepare and build embedded commands on the fly and then add them to the global
                     * scope list which is validated later
                     */
                    $result = $this->getEmbeddedCommands($attribute, $values[$attribute_name]);
                    if ($result instanceof Success) {
                        /* @var EmbeddedEntityTypeCommandList $child_commands */
                        $child_commands = $this->command_state['embedded_entity_commands'];
                        $this->command_state['embedded_entity_commands'] = $child_commands->withItems($result->get());
                        continue;
                    }
                } else {
                    $result = $this->sanitizeAttributeValue($attribute, $values[$attribute_name]);
                }

                if ($result instanceof Success) {
                    $sanitized_values[$attribute_name] = $result->get();
                } elseif ($result instanceof Error) {
                    $errors[] = $result->get();
                }
            } else {
                // weak assumption that mandatory option only applies to creation/add commands
                if ($attribute->getOption('mandatory', false) === true
                    && (is_subclass_of($this->command_class, CreateAggregateRootCommand::CLASS)
                        || $this->command_class === AddEmbeddedEntityCommand::CLASS)
                ) {
                    $errors[][$attribute_name]['@incidents'][] = [
                        'path' => $attribute->toTypePath(),
                        'incidents' => [ 'mandatory' => [ 'reason' => 'missing' ] ]
                    ];
                }
            }
        }

        return empty($errors) ? Success::unit($sanitized_values) : Error::unit($errors);
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed $value
     *
     * @return \Shrink0r\Monatic\Result
     */
    protected function sanitizeAttributeValue(AttributeInterface $attribute, $value)
    {
        try {
            $result = Success::unit($attribute->createValue($value)->toNative());
        } catch (\Exception $error) {
            $result =  Error::unit($error);
        }

        return $result;
    }

    /**
     * @param EntityInterface $entity
     * @param array $payload
     *
     * @return mixed[]
     */
    protected function filterUnmodifiedValues(EntityInterface $entity, array $payload)
    {
        return $entity->withValues($payload)->diff($entity, true);
    }
}
