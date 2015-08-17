<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\CriteriaContainerInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaInterface;
use Trellis\Common\Collection\TypedList;

class CriteriaList extends TypedList implements CriteriaContainerInterface
{
    const OP_AND = 'and';

    const OP_OR = 'or';

    protected $operator;

    public function __construct(array $items = [], $operator = self::OP_AND)
    {
        parent::__construct($items);

        $this->operator = $operator;
    }

    public function getCriteriaList()
    {
        return $this;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    protected function getItemImplementor()
    {
        return CriteriaInterface::CLASS;
    }

    public function __toString()
    {
        return implode(' ' . strtoupper($this->operator) . ' ', $this->items);
    }
}
