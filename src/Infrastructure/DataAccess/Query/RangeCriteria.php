<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Trellis\Common\Collection\TypedList;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaInterface;

class RangeCriteria extends TypedList implements CriteriaInterface
{
    protected $attribute_path;

    public function __construct($attribute_path, Comparison $first, Comparison $second = null)
    {
        $this->attribute_path = $attribute_path;
        parent::__construct([ $first ]);
        if (!is_null($second)) {
            $this->addItem($second);
        }
    }

    public function getAttributePath()
    {
        return $this->attribute_path;
    }

    protected function getItemImplementor()
    {
        return Comparison::CLASS;
    }

    public function __toString()
    {
        return sprintf(
            'ATTRIBUTE %s RANGE %s',
            $this->attribute_path,
            implode(' AND ', $this->items)
        );
    }
}
