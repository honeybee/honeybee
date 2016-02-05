<?php

namespace Honeybee\Infrastructure\Job;

interface JobInterface
{
    public function run(array $parameters = []);
}
