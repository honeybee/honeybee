<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\RuntimeError;
use JsonSerializable;
use Exception;

class StatusReport implements JsonSerializable
{
    protected $status = Status::UNKNOWN;

    protected $stats = [
        'overall' => 0,
        'failing' => 0,
        'working' => 0,
        'unknown' => 0
    ];

    protected $details = [];

    public function __construct($status, array $stats, array $details)
    {
        if (!is_string($status) || (is_string($status) && !in_array($status, Status::$states, true))) {
            throw new RuntimeError('Status must be a one of the known states: WORKING, FAILING or UNKNOWN.');
        }

        $this->status = $status;
        $this->stats = array_merge($this->stats, $stats);
        $this->details = $details;
    }

    /**
     * Generates a new StatusReport by getting the Status from all known connections.
     *
     * @param ConnectorMap $connector_map
     *
     * @return StatusReport
     */
    public static function generate(ConnectorMap $connector_map)
    {
        $details = [];
        $failing = 0;
        $working = 0;
        $unknown = 0;

        $connections = [];
        foreach ($connector_map as $name => $connector) {
            try {
                $connections[$name] = $connector->getStatus();
            } catch (Exception $e) {
                $connections[$name] = Status::failing(
                    $connector,
                    [ 'message' => 'Exception on getStatus(): ' . $e->getMessage() ]
                );
                error_log('Error while getting status of connection "' . $name . '": ' . $e->getTraceAsString());
            }
        }

        ksort($connections);

        foreach ($connections as $name => $connection) {
            $details[$name] = $connection->toArray();

            if ($connection->isFailing()) {
                $failing++;
            } elseif ($connection->isWorking()) {
                $working++;
            } else {
                $unknown++;
            }
        }

        $overall = $connector_map->count();

        $status = Status::UNKNOWN;
        if ($failing > 0) {
            $status = Status::FAILING;
        } elseif ($working === $overall) {
            $status = Status::WORKING;
        }

        $stats = [
            'overall' => $overall,
            'failing' => $failing,
            'working' => $working,
            'unknown' => $unknown
        ];

        return new static($status, $stats, $details);
    }

    /**
     * @return array data which can be serialized by json_encode
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Returns the settings as an associative array.
     *
     * @return array with all settings
     */
    public function toArray()
    {
        return [
            'status' => $this->status,
            'stats' => $this->stats,
            'details' => $this->details
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->status;
    }
}
