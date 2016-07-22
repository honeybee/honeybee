<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface CriteriaQueryInterface extends QueryInterface
{
    public function getOffset();

    public function getLimit();

    public function getFilterCriteriaList();

    public function getSearchCriteriaList();

    public function getSortCriteriaList();
}
