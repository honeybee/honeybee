<?php

namespace Honeybee\Infrastructure\Security\Acl;

interface AclServiceInterface
{
    const ROLE_ADMIN = 'administrator';

    const ROLE_NON_PRIV = 'non-privileged';

    public static function getDefaultRoles();

    public function getAclForRole($role_id, array $parent_roles = array());
}
