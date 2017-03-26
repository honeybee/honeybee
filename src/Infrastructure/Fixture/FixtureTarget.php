<?php

namespace Honeybee\Infrastructure\Fixture;

class FixtureTarget implements FixtureTargetInterface
{
    protected $name;

    protected $is_activated;

    protected $fixture_loader;

    public function __construct($name, $is_activated, FixtureLoaderInterface $fixture_loader)
    {
        $this->name = $name;
        $this->is_activated = $is_activated === true;
        $this->fixture_loader = $fixture_loader;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isActivated()
    {
        return $this->is_activated;
    }

    public function getFixtureList()
    {
        return $this->fixture_loader->loadFixtures();
    }
}
