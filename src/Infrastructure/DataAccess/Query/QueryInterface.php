<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface QueryInterface
{
    public function getOffset();

    public function getLimit();

    public function getFilterCriteriaList();

    public function getSearchCriteriaList();

    public function getSortCriteriaList();

    public function toArray();
}
