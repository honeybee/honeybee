<?php

namespace Honeybee\Infrastructure\Fixture;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Honeybee\Model\Aggregate\AggregateRootType;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand;
use Symfony\Component\Finder\Finder;
use Trellis\Common\Object;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Entity\EntityInterface;

abstract class Fixture extends Object implements FixtureInterface
{
    protected $name;

    protected $version;

    protected $command_bus;

    protected $aggregate_root_type_map;

    protected $filesystem_service;

    public function __construct(
        AggregateRootTypeMap $aggregate_root_type_map,
        CommandBusInterface $command_bus,
        FilesystemServiceInterface $filesystem_service,
        Finder $finder,
        array $state = []
    ) {
        parent::__construct($state);
        $this->command_bus = $command_bus;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->filesystem_service = $filesystem_service;
        $this->finder = $finder;
    }

    protected function importFixture($type_name, array $fixture)
    {
        $aggregate_root_type = $this->getAggregateRootType($type_name);

        $command_payload = $this->getValidatedAggregateRootCommandPayload(
            $aggregate_root_type,
            $fixture
        );

        $type_class_name = get_class($aggregate_root_type);
        $command_namespace = array_slice(explode('\\', $type_class_name), 0, -2);
        $command_class_name = sprintf(
            '%1$s\\Task\\Create%2$s\\Create%2$sCommand',
            implode('\\', $command_namespace),
            $aggregate_root_type->getName()
        );

        $command = new $command_class_name(
            [ 'aggregate_root_type' => $type_class_name ] + $command_payload
        );

        $this->command_bus->post($command);
    }

    protected function importFixtureFromFile($filename)
    {
        if (!is_readable($filename)) {
            throw new RuntimeError(sprintf('Fixture data is not readable at "%s"', $filename));
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        if (empty($data) || !is_array($data)) {
            throw new RuntimeError(sprintf('Fixture data is invalid at "%s"', $filename));
        }

        foreach ($data as $type_name => $fixtures) {
            foreach ($fixtures as $fixture) {
                $this->importFixture($type_name, $fixture);
            }
        }
    }

    protected function copyFilesToTempLocation($folder)
    {
        if (empty($folder) || !is_readable($folder)) {
            return;
        }

        $finder = $this->finder->create();
        $finder->files()->in($folder);
        $files = iterator_to_array($finder, true);

        $temp_filesystem = $this->filesystem_service->getFilesystem(
            $this->filesystem_service->getTempScheme()
        );

        // copy all fixture files into the application's temp file storage
        foreach ($files as $file) {
            $source_file_stream = fopen($file->getRealpath(), 'rb');
            if (false === $source_file_stream) {
                throw new RuntimeError(
                    sprintf(
                        'Could not open read stream to fixture file: %s',
                        $file->getRealpath()
                    )
                );
            }

            // e.g. tempfiles://fixture/file.jpg
            $target_tempfile_uri = $this->filesystem_service->createTempUri($file->getRelativePathname());

            // use putStream here to replace already existing fixture files instead of failing
            $success = $this->filesystem_service->putStream($target_tempfile_uri, $source_file_stream);
            if (!$success) {
                throw new RuntimeError(
                    sprintf(
                        'Writing fixture file "%s" to temp storage "%s" failed.',
                        $file->getRealpath(),
                        $target_tempfile_uri
                    )
                );
            }
        }
    }

    protected function getValidatedAggregateRootCommandPayload(
        AggregateRootTypeInterface $aggregate_root_type,
        array $payload
    ) {
        $aggregate_root = $aggregate_root_type->createEntity();
        $embedded_entities_data = [];
        $changed_values = [];

        foreach ($aggregate_root_type->getAttributes() as $attribute_name => $attribute) {
            if (!isset($payload[$attribute_name])) {
                continue;
            }

            if ($attribute instanceof EmbeddedEntityListAttribute) {
                $embedded_entities_data[$attribute_name] = $payload[$attribute_name];
            } else {
                // @todo santize payload
                $changed_values[$attribute_name] = $payload[$attribute_name];
            }
        }

        $embedded_commands = $this->createEmbeddedEntityCommands($aggregate_root, $embedded_entities_data);

        return [
            'values' => $changed_values,
            'embedded_entity_commands' => $embedded_commands
        ];
    }

    protected function createEmbeddedEntityCommands(EntityInterface $entity, array $payload)
    {
        $embedded_commands = [];
        foreach ($entity->getType()->getAttributes() as $attribute_name => $attribute) {
            if (!$attribute instanceof EmbeddedEntityListAttribute
                || !array_key_exists($attribute_name, $payload)
            ) {
                continue;
            }

            if (is_array($payload[$attribute_name])) {
                foreach ($payload[$attribute_name] as $position => $embedded_entity_payload) {
                    $entity_command = $this->createAddEmbeddedEntityCommand(
                        $entity->getType()->getAttribute($attribute_name),
                        $embedded_entity_payload,
                        $position
                    );
                    $embedded_commands[] = $entity_command;
                }
            }
        }

        return $embedded_commands;
    }

    protected function createAddEmbeddedEntityCommand(
        EmbeddedEntityListAttribute $embed_attribute,
        array $entity_data,
        $position
    ) {
        $embedded_entity_type = $embed_attribute->getEmbeddedEntityTypeMap()->getItem(
            $entity_data[ModifyEmbeddedEntityCommand::OBJECT_TYPE]
        );

        $temp_entity = $embedded_entity_type->createEntity();
        list($values, $embedded_data) = $this->getValidatedEmbeddedEntityPayload($temp_entity, $entity_data, $position);
        $embedded_commands = $this->createEmbeddedEntityCommands($temp_entity, $embedded_data);
        if (!empty($values) || !empty($embedded_commands)) {
            return new AddEmbeddedEntityCommand(
                [
                    'embedded_entity_type' => $embedded_entity_type->getPrefix(),
                    'parent_attribute_name' => $embed_attribute->getName(),
                    'values' => $values,
                    'position' => $position,
                    'embedded_entity_commands' => $embedded_commands
                ]
            );
        }

        return null;
    }

    protected function getValidatedEmbeddedEntityPayload(EntityInterface $entity, array $payload, $position)
    {
        $entity_payload = [];
        $embedded_payload = [];

        foreach ($entity->getType()->getAttributes() as $attribute_name => $attribute) {
            if (!isset($payload[$attribute_name])) {
                continue;
            }

            if ($attribute instanceof EmbeddedEntityListAttribute) {
                $embedded_payload[$attribute_name] = $payload[$attribute_name];
            } else {
                // @todo santize payload
                $entity_payload[$attribute_name] = $payload[$attribute_name];
            }
        }

        return [ $entity_payload, $embedded_payload ];
    }

    final public function execute(FixtureTargetInterface $fixture_target)
    {
        $this->guardFixtureTarget($fixture_target);
        $this->import($fixture_target);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    protected function getAggregateRootType($type_name)
    {
        $aggregate_root_type = $this->aggregate_root_type_map->getItem($type_name);
        if (!$aggregate_root_type instanceof AggregateRootType) {
            throw new RuntimeError(sprintf('No aggregate root type found with prefix: %s', $type_name));
        }

        return $aggregate_root_type;
    }

    protected function guardFixtureTarget(FixtureTargetInterface $fixture_target)
    {
        if (!$fixture_target->isActivated()) {
            throw new RuntimeError(
                sprintf(
                    'Not allowed to execute fixture %s:%s, fixture target %s is deactivated.',
                    $this->getVersion(),
                    $this->getName(),
                    $fixture_target->getName()
                )
            );
        }
    }
}
