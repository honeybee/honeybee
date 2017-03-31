<?php

namespace Honeybee\Infrastructure\Fixture;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Trellis\Sham\DataGenerator;

class FixtureService implements FixtureServiceInterface
{
    protected $config;

    protected $fixture_target_map;

    protected $aggregate_root_type_map;

    protected $data_generator;

    protected static $excluded_attributes = [
        'workflow_state',
        'workflow_parameters',
        'created_at',
        'modified_at'
    ];

    public function __construct(
        ConfigInterface $config,
        FixtureTargetMap $fixture_target_map,
        AggregateRootTypeMap $aggregate_root_type_map,
        DataGenerator $data_generator = null
    ) {
        $this->config = $config;
        $this->fixture_target_map = $fixture_target_map;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->data_generator = $data_generator ?: new DataGenerator;
    }

    public function import($target_name, $fixture_name)
    {
        $fixtures = $this->getFixtureList($target_name)->filter(
            function (FixtureInterface $fixture) use ($fixture_name) {
                return $fixture->getVersion() . ':' . $fixture->getName() == $fixture_name;
            }
        );

        $fixture_count = count($fixtures);
        if ($fixture_count !== 1) {
            throw new RuntimeError("Expected one fixture for name '$fixture_name', but found: " . $fixture_count);
        }

        $fixture = $fixtures->getFirst();
        $fixture->execute($this->getFixtureTarget($target_name));

        return $fixture;
    }

    public function getFixtureTargetMap()
    {
        return $this->fixture_target_map;
    }

    public function getFixtureList($target_name)
    {
        return $this->getFixtureTarget($target_name)->getFixtureList();
    }

    public function getFixtureTarget($target_name)
    {
        if (!$this->fixture_target_map->hasKey($target_name)) {
            throw new RuntimeError(sprintf("Unable to find fixture target %s.", $target_name));
        }

        return $this->fixture_target_map->getItem($target_name);
    }

    public function generate($type_prefix, $size = 1, $locale = 'de_DE')
    {
        $aggregate_root_type = $this->aggregate_root_type_map->getItem($type_prefix);

        $documents = [];
        $options = [
            DataGenerator::OPTION_LOCALE => $locale,
            DataGenerator::OPTION_EXCLUDED_FIELDS => self::$excluded_attributes,
            DataGenerator::OPTION_FIELD_VALUES => [
                'language' => $locale,
                'referenced_identifier' => '**REFERENCE ID REQUIRED**'
            ]
        ];

        for ($cnt = 0; $cnt < $size; $cnt++) {
            $document = $this->data_generator->createDataFor($aggregate_root_type, $options);
            $this->excludeAttributes($document);

            // Add identifier for convenient related entity referencing purposes
            $identifier = sprintf(
                '%s-%s-%s-%s',
                $aggregate_root_type->getPrefix(),
                $document['uuid'],
                $document['language'],
                $document['version']
            );
            $documents[] = [ 'identifier' => $identifier ] + $document;
        }

        return $documents;
    }

    protected function excludeAttributes(array &$array)
    {
        foreach (self::$excluded_attributes as $excluded_attribute) {
            if (array_key_exists($excluded_attribute, $array)) {
                unset($array[$excluded_attribute]);
            }
        }

        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->excludeAttributes($value);
            }
        }
    }
}
