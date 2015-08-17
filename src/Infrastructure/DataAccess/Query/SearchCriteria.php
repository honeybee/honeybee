<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

class SearchCriteria implements CriteriaInterface
{
    protected $attribute_path;

    protected $phrase;

    public function __construct($phrase, $attribute_path = null)
    {
        $this->attribute_path = $attribute_path;
        $this->phrase = $phrase;
    }

    public function getAttributePath()
    {
        return $this->attribute_path;
    }

    public function getPhrase()
    {
        return $this->phrase;
    }

    public function __toString()
    {
        return sprintf(
            'FOR %s%s',
            $this->phrase,
            $this->attribute_path ? (' ON ATTRIBUTE ' . $this->attribute_path) : ''
        );
    }
}
