<?php

namespace Honeybee\Model\Command\Bulk;

use Trellis\Common\BaseObject;

class BulkOperation extends BaseObject implements BulkOperationInterface
{
    protected $metadata;

    protected $payload;

    public function __construct(BulkMetadata $metadata, $payload)
    {
        $this->metadata = $metadata;
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
}
