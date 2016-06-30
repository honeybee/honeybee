<?php

namespace Honeybee\Model\Command;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\EntityInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Projection\ProjectionInterface;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Success;

class AggregateRootCommandBuilder extends EmbeddedEntityCommandBuilder
{
    protected $entity;

    public function __construct(AggregateRootTypeInterface $aggregate_root_type, $command_class)
    {
        parent::__construct($aggregate_root_type, $command_class);

        $this->command_state['aggregate_root_type'] = $aggregate_root_type->getPrefix();
    }

    public function build()
    {
        $result = parent::build();
        if ($result instanceof Error) {
            $result = Error::unit(self::flatten($result->get()));
        }

        return $result;
    }

    public function fromEntity(EntityInterface $entity)
    {
        if (!$entity instanceof ProjectionInterface &&
            !$entity instanceof AggregateRootInterface
        ) {
            throw new RuntimeError(sprintf(
                'Provided %s must implement %s or %s.',
                get_class($entity),
                ProjectionInterface::CLASS,
                AggregateRootInterface::CLASS
            ));
        }

        $this->entity = $entity;
        $this->command_state['aggregate_root_identifier'] = $entity->getIdentifier();
        $this->command_state['known_revision'] = $entity->getRevision();
        return $this;
    }

    /**
     * @return Result
     */
    protected function validateValues(array $values)
    {
        $result = parent::validateValues($values);

        if (isset($this->projection) && $result instanceof Success) {
            $modified_values = $this->filterUnmodifiedValues($this->projection, $result->get());
            $result = Success::unit($modified_values);
        }

        return $result;
    }

    protected static function flatten(array $array, $parent_prefix = '')
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            $key = $parent_prefix . $key;
            $key = preg_replace('/(^\.|\.?(values|embedded_entity_commands)\.(\d|\.)?\.?)/', '', $key);
            if (is_array($value)) {
                if (isset($value['@incidents'])) {
                    $flattened[$key] = $value['@incidents'];
                } else {
                    $flattened = array_merge(self::flatten($value, $key . '.'), $flattened);
                }
            } else {
                $flattened[$key] = $value;
            }
        }

        return $flattened;
    }
}
