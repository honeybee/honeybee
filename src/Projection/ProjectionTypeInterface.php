<?php

namespace Honeybee\Projection;

interface ProjectionTypeInterface
{
    const DEFAULT_VARIANT = 'Standard';

    public function getPrefix();

    public function getVariantPrefix();
}
