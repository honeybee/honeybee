<?php

namespace Honeybee\Infrastructure\Fixture;

interface FixtureInterface
{
    public function import(FixtureTargetInterface $fixture_target);

    public function getName();

    public function getVersion();
}
