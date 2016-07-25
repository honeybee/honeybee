<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface CriteriaQueryInterface extends QueryInterface
{
    public function getFilterCriteriaList();

    public function getSearchCriteriaList();

    public function getSortCriteriaList();
}
