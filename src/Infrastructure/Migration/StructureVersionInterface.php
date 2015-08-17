<?php

namespace Honeybee\Infrastructure\Migration;

interface StructureVersionInterface
{
    public function getTargetName();

    public function getVersion();
}
