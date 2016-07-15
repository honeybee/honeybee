<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface DomainEventQueryServiceInterface extends QueryServiceInterface
{
    public function findEventsByIdentifier($identifier, $offset = 0, $limit = 10000);
}
