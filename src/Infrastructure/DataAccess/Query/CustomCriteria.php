<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Trellis\Common\Object;

/**
 * Criteria that represents a custom query part. The query translation for
 * this probably doesn't need to translate anything as it is to be used for
 * situations where the query DSL is not sufficient but going to a full
 * CustomQuery is not wanted either.
 */
class CustomCriteria extends Object implements CriteriaInterface
{
    protected $query_part;

    /**
     * @param mixed $query_part custom part of a query
     */
    public function __construct($query_part)
    {
        $this->query_part = $query_part;
    }

    /**
     * @return mixed
     */
    public function getQueryPart()
    {
        return $this->query_part;
    }

    public function __toString()
    {
        return 'CUSTOM PART ' . json_encode($this->query_part, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
}
