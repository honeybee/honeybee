<?php

namespace Honeybee\Tests\Infrastructure\Fixture\Fixture;

use Honeybee\Infrastructure\Fixture\Fixture;
use Honeybee\Infrastructure\Fixture\FixtureTargetInterface;

class MockFixture extends Fixture
{
    protected $fixture;

    protected function import(FixtureTargetInterface $fixture_target)
    {
        $this->copyFilesToTempLocation(__DIR__ . DIRECTORY_SEPARATOR . 'files');

        foreach ($this->getFixtureData() as $filename) {
            $this->importFixtureFromFile($filename);
        }
    }

    public function setFixtureData($fixture)
    {
        $this->fixture = $fixture;
    }

    protected function getFixtureData()
    {
        // one data set per fixture and one associated command expectation
        return [
            __DIR__ . DIRECTORY_SEPARATOR . $this->fixture
        ];
    }
}
