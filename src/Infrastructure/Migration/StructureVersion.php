<?php

namespace Honeybee\Infrastructure\Migration;

class StructureVersion implements StructureVersionInterface
{
    protected $target_name;

    protected $version;

    protected $created_date;

    public function __construct(array $state = [])
    {
        $this->created_date = date(DATE_ISO8601);

        foreach ($state as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    public function getTargetName()
    {
        return $this->target_name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getCreatedDate()
    {
        return $this->created_date;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
