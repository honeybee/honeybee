<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class PermissionList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $permissions = [])
    {
        parent::__construct(Permission::CLASS, $permissions);
    }

    public function __toString()
    {
        $permissions = '';

        foreach ($this->items as $permission) {
            $permissions .= $permission . PHP_EOL;
        }

        return $permissions;
    }
}
