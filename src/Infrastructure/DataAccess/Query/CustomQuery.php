<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Assert\Assertion;
use Honeybee\Common\Util\ArrayToolkit;

abstract class CustomQuery implements CustomQueryInterface
{
    protected $parameters;

    protected $offset;

    protected $limit;

    public function __construct(array $parameters, $offset, $limit)
    {
        Assertion::integer($offset);
        Assertion::min($offset, 0);
        Assertion::integer($limit);
        Assertion::min($limit, 1);

        $this->parameters = $parameters;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    abstract public function getQuery();

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function __toString()
    {
        return sprintf(
            'CUSTOM QUERY: SEARCH %s PARAMS %s %s %s',
            substr(static::CLASS, strrpos(static::CLASS, '\\') + 1),
            http_build_query(ArrayToolkit::flatten($this->parameters), '', ', '),
            isset($this->limit) ? ('LIMIT ' . $this->limit) : '',
            isset($this->offset) ? ('OFFSET ' . $this->offset) : ''
        );
    }
}
