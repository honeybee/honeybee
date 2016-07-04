<?php

namespace Honeybee\Infrastructure\Fixture;

class FixtureTarget implements FixtureTargetInterface
{
    protected $name;

    protected $is_activated;

    protected $fixture_list;

    protected $fixture_loader;

    public function __construct(
        $name,
        $is_activated,
        FixtureLoaderInterface $fixture_loader
    ) {
        $this->name = $name;
        $this->is_activated = $is_activated;
        $this->fixture_loader = $fixture_loader;
    }

    public function getFixtureList()
    {
        if (!$this->fixture_list) {
            $this->fixture_list = $this->fixture_loader->loadFixtures();
        }

        return $this->fixture_list;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isActivated()
    {
        return $this->is_activated;
    }
}
