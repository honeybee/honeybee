<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Assert\Assertion;
use Honeybee\Common\Util\ArrayToolkit;

class StoredQuery implements StoredQueryInterface
{
    protected $name;

    protected $parameters;

    protected $offset;

    protected $limit;

    public function __construct($name, array $parameters, $offset, $limit)
    {
        Assertion::string($name);
        Assertion::notEmpty($name);
        Assertion::integer($offset);
        Assertion::min($offset, 0);
        Assertion::integer($limit);
        Assertion::min($limit, 1);

        $this->name = $name;
        $this->parameters = $parameters;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function getName()
    {
        return $this->name;
    }

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
            'STORED QUERY: SEARCH %s PARAMS %s %s %s',
            $this->name,
            http_build_query(ArrayToolkit::flatten($this->parameters), '', ', '),
            isset($this->limit) ? ('LIMIT ' . $this->limit) : '',
            isset($this->offset) ? ('OFFSET ' . $this->offset) : ''
        );
    }
}
