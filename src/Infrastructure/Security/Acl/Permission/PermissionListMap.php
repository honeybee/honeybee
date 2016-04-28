<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class PermissionListMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return PermissionList::CLASS;
    }
}
