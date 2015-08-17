<?php

namespace Honeybee\Infrastructure\Migration;

interface MigrationTargetInterface
{
    public function getName();

    public function getMigrationList();

    public function isActivated();

    public function getLatestStructureVersion();

    public function getStructureVersionList();

    public function bumpStructureVersion(MigrationInterface $migration, $direction);

    public function getConfig();
}
