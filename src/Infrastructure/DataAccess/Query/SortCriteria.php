<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

class SortCriteria implements CriteriaInterface
{
    const SORT_ASC = 'asc';

    const SORT_DESC = 'desc';

    protected $attribute_path;

    protected $direction;

    public function __construct($attribute_path, $direction = self::SORT_ASC)
    {
        $this->attribute_path = $attribute_path;
        $this->direction = $direction;
    }

    public function getAttributePath()
    {
        return $this->attribute_path;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function __toString()
    {
        return sprintf(
            'BY %s %s',
            $this->attribute_path,
            strtoupper($this->direction)
        );
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
