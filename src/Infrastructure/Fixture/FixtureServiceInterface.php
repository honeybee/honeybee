<?php

namespace Honeybee\Infrastructure\Fixture;

interface FixtureServiceInterface
{
    public function import($target_name, $fixture_name);

    public function generate($type_prefix);

    public function getFixtureTargetMap();

    public function getFixtureList($target_name);

    public function getFixtureTarget($target_name);
}
