<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface CriteriaContainerInterface extends CriteriaInterface
{
    public function getCriteriaList();

    public function getOperator();
}
