<?php

namespace Honeybee\Model\Command\Bulk;

use Trellis\Common\Object;

class BulkOperation extends Object implements BulkOperationInterface
{
    protected $meta_data;

    protected $payload;

    public function __construct(BulkMetaData $meta_data, $payload)
    {
        $this->meta_data = $meta_data;
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getMetaData()
    {
        return $this->meta_data;
    }
}
