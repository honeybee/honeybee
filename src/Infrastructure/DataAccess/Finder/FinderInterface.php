<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

interface FinderInterface
{
    public function find($query);

    public function scrollStart($query, $cursor = null);

    public function scrollNext($cursor, $size = null);

    public function scrollEnd($cursor);

    public function findByStored($query);

    public function getByIdentifier($identifier);

    public function getByIdentifiers(array $identifiers);
}
