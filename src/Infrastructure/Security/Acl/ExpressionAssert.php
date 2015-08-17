<?php

namespace Honeybee\Infrastructure\Security\Acl;

use Trellis\Common\Configurable;
use Honeybee\Model\Aggregate\AggregateRoot;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;

class ExpressionAssert extends Configurable implements AssertionInterface
{
    protected $expression;

    protected $expression_service;

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        if (!($resource instanceof AggregateRoot)) {
            return false;
        }

        return $this->expression_service->evaluate(
            $this->expression,
            array_merge(
                $this->getOptions()->toArray(),
                array('resource' => $resource, 'user' => $role)
            )
        );
    }
}
