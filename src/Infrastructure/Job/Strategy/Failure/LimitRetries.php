<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\JobInterface;

class LimitRetries implements FailureStrategyInterface
{
    protected $limit;

    public function __construct(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        if (!$settings->has('limit')) {
            throw new RuntimeError('LimitRetries strategy requires "limit" setting.');
        }

        $this->limit = $settings->get('limit');
    }

    public function hasFailed(JobInterface $job)
    {
        $meta_data = $job->getMetaData();
        $retries = isset($meta_data['retries']) ? $meta_data['retries'] : 0;
        return $retries >= $this->limit;
    }
}
