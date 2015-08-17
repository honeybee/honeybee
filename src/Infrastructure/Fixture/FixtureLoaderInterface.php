<?php

namespace Honeybee\Infrastructure\Fixture;

interface FixtureLoaderInterface
{
    /**
     * @return FixtureList
     */
    public function loadFixtures();
}
