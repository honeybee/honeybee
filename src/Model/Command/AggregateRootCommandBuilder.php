<?php

namespace Honeybee\Model\Command;

use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Shrink0r\Monatic\Error;

class AggregateRootCommandBuilder extends EmbeddedEntityCommandBuilder
{
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
