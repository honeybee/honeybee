<?php

namespace Honeybee\Infrastructure\Security\Acl;

use Honeybee\Model\Aggregate\AggregateRoot;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;

class ExpressionAssert implements AssertionInterface
{
    protected $expression;

    protected $expression_service;

    protected $options = [];

    public function __construct(array $state = [])
    {
        foreach ($state as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        if (!($resource instanceof AggregateRoot)) {
            return false;
        }

        return $this->expression_service->evaluate(
            $this->expression,
            array_merge(
                $this->options,
                [ 'resource' => $resource, 'user' => $role ]
            )
        );
    }
}
