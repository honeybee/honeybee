<?php

namespace Honeybee\Common;

class GenericScopeKey implements ScopeKeyInterface
{
    protected $scope_key;

    public function __construct($scope_key)
    {
        $this->scope_key = $scope_key;
    }

    public function getScopeKey()
    {
        return $this->scope_key;
    }

    public function __toString()
    {
        return (string)$this->scope_key;
    }
}
