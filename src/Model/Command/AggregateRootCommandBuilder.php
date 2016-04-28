<?php

namespace Honeybee\Model\Command;

use Honeybee\Projection\ProjectionInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Shrink0r\Monatic\Error;
use Shrink0r\Monatic\Success;

class AggregateRootCommandBuilder extends EmbeddedEntityCommandBuilder
{
    protected $projection;

    public function __construct(AggregateRootTypeInterface $aggregate_root_type, $command_class)
    {
        parent::__construct($aggregate_root_type, $command_class);

        $this->command_state['aggregate_root_type'] = get_class($aggregate_root_type);
    }

    public function build()
    {
        $result = parent::build();
        if ($result instanceof Error) {
            $result = Error::unit(self::flatten($result->get()));
        }

        return $result;
    }

    public function withProjection(ProjectionInterface $projection)
    {
        $this->projection = $projection;
        $this->command_state['aggregate_root_identifier'] = $projection->getIdentifier();
        $this->command_state['known_revision'] = $projection->getRevision();
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
