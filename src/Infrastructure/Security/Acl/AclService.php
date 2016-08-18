<?php

namespace Honeybee\Infrastructure\Security\Acl;

use Trellis\Common\Object;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Security\Acl\Permission\Permission;
use Honeybee\Infrastructure\Security\Acl\Permission\PermissionListMap;
use Honeybee\Infrastructure\Security\Acl\Permission\PermissionServiceInterface;
use Honeybee\Ui\Activity\ActivityServiceInterface;
use Honeybee\Infrastructure\Expression\ExpressionServiceInterface;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Acl;

class AclService extends Object implements AclServiceInterface
{
    /**
     * @var array ids of roles defined by default
     */
    protected static $default_roles = [
        self::ROLE_FULL_PRIV,
        self::ROLE_NON_PRIV
    ];

    protected $roles_configuration;

    protected $activity_service;

    protected $expression_service;

    protected $permission_service;

    public function __construct(
        ActivityServiceInterface $activity_service,
        PermissionServiceInterface $permission_service,
        ExpressionServiceInterface $expression_service,
        array $roles_configuration
    ) {
        $this->activity_service = $activity_service;
        $this->permission_service = $permission_service;
        $this->expression_service = $expression_service;
        $this->roles_configuration = $roles_configuration;
    }

    /**
     * @return array ids of roles always available by default
     */
    public static function getDefaultRoles()
    {
        return self::$default_roles;
    }

    /**
     * @return array ids of configured roles
     */
    public function getRoles()
    {
        return array_keys($this->roles_configuration['roles']);
    }

    /**
     * @return Zend\Permissions\Acl\Acl access control list for the given role id
     */
    public function getAclForRole($role_id)
    {
        $default_acl = $this->createDefaultAcl();

        if (!in_array($role_id, self::$default_roles)) {
            // apply permissions for custom roles
            $this->registerRole($role_id, $default_acl);
        }

        return $default_acl;
    }

    /**
     * @param string $role_id
     *
     * @return array ids of parent roles of the given role
     */
    public function getRoleParents($role_id)
    {
        $parents = [];

        if (!$role_id || in_array($role_id, self::$default_roles)) {
            return $parents;
        }

        $role_configuration = $this->roles_configuration['roles'][$role_id];
        $parent_role_id = $role_configuration['parent'];

        while (!empty($parent_role_id)) {
            $parents[] = $parent_role_id;
            if (isset($this->roles_configuration['roles'][$parent_role_id])) {
                $role_configuration = $this->roles_configuration['roles'][$parent_role_id];
                $parent_role_id = $role_configuration['parent'];
            } else {
                $parent_role_id = null;
            }
        }

        return $parents;
    }

    protected function createDefaultAcl()
    {
        $access_control_list = new Acl();

        // add resources
        $all_permissions = $this->permission_service->getGlobalPermissions();
        foreach ($all_permissions as $access_scope => $permissions) {
            $access_control_list->addResource(new GenericResource($access_scope));
        }

        // add full-privileged administrator; allow all on all resource
        $admin_role = new GenericRole(self::ROLE_FULL_PRIV);

        $access_control_list->addRole($admin_role);
        $access_control_list->allow($admin_role);

        // add non-privileged role; allow nothing nowhere
        $access_control_list->addRole(new GenericRole(self::ROLE_NON_PRIV));

        return $access_control_list;
    }

    protected function registerRole($role_id, Acl $role_acl)
    {
        if (!isset($this->roles_configuration['roles'][$role_id])) {
            throw new RuntimeError("Trying to load acl for non-existant role: " . $role_id);
        }

        $role = null;
        $role_configuration = $this->roles_configuration['roles'][$role_id];
        $parent_role_id = $role_configuration['parent'];

        if (!empty($parent_role_id)) {
            if (!in_array($parent_role_id, self::$default_roles)) {
                $this->registerRole($parent_role_id, $role_acl);
            }
            $role = new GenericRole($role_id);
            $role_acl->addRole($role, [ $parent_role_id ]);
        } else {
            $role = new GenericRole($role_id);
            $role_acl->addRole($role);
        }

        $role_permissions = $this->permission_service->getRolePermissions($role->getRoleId());
        if ($role_permissions) {
            $this->applyRolePermissions($role_acl, $role, $role_permissions);
        }

        return $role_acl;
    }

    protected function applyRolePermissions(Acl $role_acl, GenericRole $role, PermissionListMap $role_permissions_map)
    {
        $permitted_scopes = [];

        foreach ($role_permissions_map as $scope => $role_permissions) {
            foreach ($role_permissions as $permission) {
                $expression = $permission->getExpression();
                $expression_assert = null;
                if (!empty($expression)) {
                    $expression_assert = new ExpressionAssert(
                        [ 'expression' => $expression, 'expression_service' => $this->expression_service ]
                    );
                }

                if ($permission->getAccessType() === Permission::ALLOW) {
                    $role_acl->allow($role, $permission->getAccessScope(), $permission->getName());
                    // implicitly register a global right for each scope (to serve for example: action.getCredentials)
                    if (!in_array($permission->getAccessScope(), $permitted_scopes)) {
                        $permitted_scopes[] = $permission->getAccessScope();
                        $role_acl->allow($role, null, $permission->getAccessScope(), $expression_assert);
                    }
                } else {
                    $role_acl->deny($role, $permission->getAccessScope(), $permission->getName(), $expression_assert);
                }
            }
        }
    }
}
