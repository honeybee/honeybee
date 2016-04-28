<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\JobInterface;

class LimitRetries implements FailureStrategyInterface
{
    protected $job;

    protected $limit;

    public function __construct(JobInterface $job, SettingsInterface $settings)
    {
        if (!$settings->has('limit')) {
            throw new RuntimeError('LimitRetries strategy requires "limit" setting.');
        }

        $this->job = $job;
        $this->limit = $settings->get('limit');
    }

    public function hasFailed()
    {
        $metadata = $this->job->getMetadata();
        $retries = isset($metadata['retries']) ? $metadata['retries'] : 0;
        return $retries >= $this->limit;
    }
}
