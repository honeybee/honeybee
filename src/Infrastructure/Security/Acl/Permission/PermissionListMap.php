<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class PermissionListMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $permission_lists = [])
    {
        parent::__construct(PermissionList::CLASS, $permission_lists);
    }
}
