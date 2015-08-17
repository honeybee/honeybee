<?php

namespace Honeybee\Model\Command\Bulk;

interface BulkOperationInterface
{
    public function getPayload();

    public function getMetaData();
}
