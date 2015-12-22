<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Flysystem;

use Exception;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Honeybee\Infrastructure\DataAccess\Connector\Status;

abstract class AbstractFlysystemConnector extends Connector
{
    /**
     * Tries to access a (maybe non-existant) file path to see whether the connection works
     * correctly. Configure the 'status_test_path' to check for an actual existant file path.
     *
     * @return Status of the configured filesystem connector
     */
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        try {
            if ($this->config->has('status_test')) {
                if ($this->getConnection()->has($this->config->get('status_test'))) {
                    return Status::working($this, [ 'message' => 'Expected file path exists.' ]);
                }
                return Status::failing($this, [ 'message' => 'Expected file path does not exist.' ]);
            }

            $this->getConnection()->has('some-probably-non-existant-filepath');
            return Status::working($this);
        } catch (Exception $e) {
            error_log('[' . static::CLASS . '] Error on file path existance check: ' . $e->getTraceAsString());
            return Status::failing(
                $this,
                [ 'message' => 'Exception on file path existance check: ' . $e->getMessage() ]
            );
        }
    }
}
