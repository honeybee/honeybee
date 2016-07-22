<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

interface FinderInterface
{
    public function find($query);

    public function findByStored($query);

    public function getByIdentifier($identifier);

    public function getByIdentifiers(array $identifiers);
}
