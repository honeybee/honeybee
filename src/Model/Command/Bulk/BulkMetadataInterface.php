<?php

namespace Honeybee\Model\Command\Bulk;

interface BulkMetadataInterface
{
    public function getType();

    public function getCommand();

    public function getIdentifier();
}
