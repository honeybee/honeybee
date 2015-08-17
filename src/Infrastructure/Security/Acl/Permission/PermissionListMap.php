<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class PermissionListMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return PermissionList::CLASS;
    }
}
