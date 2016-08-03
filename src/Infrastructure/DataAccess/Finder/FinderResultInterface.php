<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

interface FinderResultInterface
{
    public function getOffset();

    public function getResults();

    public function hasResults();

    public function getFirstResult();

    public function getTotalCount();

    public function getCount();

    public function getCursor();
}
