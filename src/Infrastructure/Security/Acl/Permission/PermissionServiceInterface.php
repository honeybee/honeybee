<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

interface PermissionServiceInterface
{
    public function getRolePermissions($role_id);

    public function getGlobalPermissions();
}
