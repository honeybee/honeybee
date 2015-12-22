<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\RuntimeError;
use JsonSerializable;

final class Status implements JsonSerializable
{
    const WORKING = 'WORKING';
    const FAILING = 'FAILING';
    const UNKNOWN = 'UNKNOWN';

    public $states = [
        self::WORKING,
        self::FAILING,
        self::UNKNOWN
    ];

    protected $connection_name;

    protected $implementor;

    protected $status;

    protected $info = [];

    public function __construct(ConnectorInterface $connector, $status, array $info = [])
    {
        if (!is_string($status) || (is_string($status) && !in_array($status, $this->states, true))) {
            throw new RuntimeError('Status must be a string out of constants: WORKING, FAILING or UNKNOWN.');
        }

        $this->connection_name = $connector->getName();
        $this->implementor = get_class($connector);
        $this->status = $status;
        $this->info = $info;
    }

    /**
     * @return Status new instance with status WORKING and given info
     */
    public static function working(ConnectorInterface $connector, array $info = [])
    {
        return new self($connector, self::WORKING, $info);
    }

    /**
     * @return Status new instance with status FAILING and given info
     */
    public static function failing(ConnectorInterface $connector, array $info = [])
    {
        return new self($connector, self::FAILING, $info);
    }

    /**
     * @return Status new instance with status UNKNOWN and given info
     */
    public static function unknown(ConnectorInterface $connector, array $info = [])
    {
        return new self($connector, self::UNKNOWN, $info);
    }

    /**
     * @return bool true, when status is WORKING
     */
    public function isWorking()
    {
        return ($this->status === self::WORKING);
    }

    /**
     * @return bool true, when status is FAILING
     */
    public function isFailing()
    {
        return ($this->status === self::FAILING);
    }

    /**
     * @return bool true, when status is UNKNOWN
     */
    public function isUnknown()
    {
        return ($this->status === self::UNKNOWN);
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connection_name;
    }

    /**
     * @return string
     */
    public function getImplementor()
    {
        return $this->implementor;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array additional information about the status (if applicable/available)
     */
    public function getInfo()
    {
        return $this->info;
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
            'connection_name' => $this->connection_name,
            'implementor' => $this->implementor,
            'info' => $this->info
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->connection_name . '=' . $this->status;
    }
}
