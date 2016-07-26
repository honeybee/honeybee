<?php

namespace Honeybee\Infrastructure\Fixture;

interface FixtureInterface
{
    public function execute(FixtureTargetInterface $fixture_target);

    public function getName();

    public function getVersion();
}
