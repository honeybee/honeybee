<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class PermissionList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return Permission::CLASS;
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
