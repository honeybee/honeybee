<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Common\BaseObject;

class StructureVersion extends BaseObject implements StructureVersionInterface
{
    protected $target_name;

    protected $version;

    protected $created_date;

    public function __construct(array $state = [])
    {
        $this->created_date = date(DATE_ISO8601);

        parent::__construct($state);
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
}
