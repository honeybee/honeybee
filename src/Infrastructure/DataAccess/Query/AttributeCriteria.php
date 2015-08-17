<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

class AttributeCriteria implements CriteriaInterface
{
    protected $attribute_path;

    protected $comparator;

    protected $value;

    public function __construct($attribute_path, $value, $comparator = self::EQUALS)
    {
        $this->attribute_path = $attribute_path;
        $this->value = $value;
        $this->comparator = $comparator;
    }

    public function getAttributePath()
    {
        return $this->attribute_path;
    }

    public function getComparator()
    {
        return $this->comparator;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return sprintf(
            'ATTRIBUTE %s %s %s',
            $this->attribute_path,
            strtoupper($this->comparator),
            $this->value
        );
    }
}
