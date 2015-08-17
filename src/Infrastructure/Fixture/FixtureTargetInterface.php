<?php

namespace Honeybee\Infrastructure\Fixture;

interface FixtureTargetInterface
{
    public function getName();

    public function getFixtureList();

    public function isActivated();
}
