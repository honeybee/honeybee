<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

interface FinderInterface
{
    public function find(array $query);

    public function findByStored(array $query);

    public function getByIdentifier($identifier);

    public function getByIdentifiers(array $identifiers);
}
