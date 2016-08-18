<?php

namespace Honeybee\Infrastructure\Security\Acl;

interface AclServiceInterface
{
    const ROLE_NON_PRIV = 'non-privileged';

    const ROLE_FULL_PRIV = 'full-privileged';

    /**
     * @return array ids of roles always available by default
     */
    public static function getDefaultRoles();

    /**
     * @return array ids of configured roles
     */
    public function getRoles();

    /**
     * @return Zend\Permissions\Acl\Acl access control list for the given role id
     */
    public function getAclForRole($role_id);

    /**
     * @param string $role_id
     *
     * @return array ids of parent roles of the given role
     */
    public function getRoleParents($role_id);
}
