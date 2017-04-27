<?php

namespace Honeybee\Infrastructure\Job;

interface JobInterface
{
    public function getUuid();

    public function getIsoDate();

    public function getMetadata();

    public function run(array $parameters = []);
}
