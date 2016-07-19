<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface ComparisonInterface
{
    public function getComparator();

    public function getComparand();

    public function isInverted();
}
