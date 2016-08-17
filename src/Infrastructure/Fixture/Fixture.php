<?php

namespace Honeybee\Infrastructure\Fixture;

use Honeybee\Common\Error\ParseError;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Psr\Log\LoggerInterface;
use Shrink0r\Monatic\Success;
use Symfony\Component\Finder\Finder;
use Trellis\Common\Object;

abstract class Fixture extends Object implements FixtureInterface
{
    protected $name;

    protected $version;

    protected $aggregate_root_type_map;

    protected $command_bus;

    protected $filesystem_service;

    protected $finder;

    protected $logger;

    public function __construct(
        AggregateRootTypeMap $aggregate_root_type_map,
        CommandBusInterface $command_bus,
        FilesystemServiceInterface $filesystem_service,
        Finder $finder,
        LoggerInterface $logger,
        array $state = []
    ) {
        parent::__construct($state);

        $this->command_bus = $command_bus;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->filesystem_service = $filesystem_service;
        $this->finder = $finder;
        $this->logger = $logger;
    }

    abstract protected function import(FixtureTargetInterface $fixture_target);

    protected function importFixture($type_name, array $fixture)
    {
        $aggregate_root_type = $this->aggregate_root_type_map->getItem($type_name);

        $type_class_name = get_class($aggregate_root_type);
        $command_namespace = array_slice(explode('\\', $type_class_name), 0, -2);
        $command_class = isset($fixture['@command'])
            ? $fixture['@command']
            : sprintf(
                '%1$s\\Task\\Create%2$s\\Create%2$sCommand',
                implode('\\', $command_namespace),
                $aggregate_root_type->getName()
            );

        $builder = new AggregateRootCommandBuilder($aggregate_root_type, $command_class);
        if (isset($fixture['@command'])) {
            unset($fixture['@command']);
            foreach ($fixture as $command_property => $command_data) {
                $builder->{'with' . StringToolkit::asCamelCase($command_property)}($command_data);
            }
        } else {
            $builder->withValues($fixture);
        }
        $result = $builder->build();

        if (!$result instanceof Success) {
            $this->logger->error('Error importing fixtures:' . print_r($result->get(), true));
            throw new RuntimeError(sprintf('Error importing fixtures. Incidents have been logged.'));
        }

        $this->command_bus->post($result->get());
    }

    protected function importFixtureFromFile($filename)
    {
        if (!is_readable($filename)) {
            throw new RuntimeError(sprintf('Fixture data is not readable at "%s"', $filename));
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        $last_error = json_last_error();
        if ($last_error !== JSON_ERROR_NONE) {
            throw new ParseError('Failed to parse json from "' . $filename . '": ' . json_last_error_msg());
        }
        if (empty($data) || !is_array($data)) {
            throw new RuntimeError('Fixture data is empty/invalid at: ' . $filename);
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
