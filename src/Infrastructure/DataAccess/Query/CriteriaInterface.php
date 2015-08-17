<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface CriteriaInterface
{
    const EQUALS = 'eq';

    const GREATER_THAN = 'gt';

    const LESS_THAN = 'lt';

    const GREATER_THAN_EQUAL = 'gte';

    const LESS_THAN_EQUAL = 'lte';
}
