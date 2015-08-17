<?php

namespace Honeybee\Model\Command\Bulk;

interface BulkMetaDataInterface
{
    public function getType();

    public function getCommand();

    public function getIdentifier();
}
