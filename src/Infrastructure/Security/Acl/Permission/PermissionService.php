<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

use Trellis\Common\Configurable;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Ui\Activity\ActivityServiceInterface;
use Honeybee\Ui\Activity\ActivityContainer;
use Honeybee\Infrastructure\Security\Acl\AclService;

class PermissionService extends Configurable implements PermissionServiceInterface
{
    const WORKFLOW_SCOPE_REGEXP = '/app\.workflow\.([\w_]+)\.[\w_]/';

    protected $activity_service;

    protected $access_config;

    protected $aggregate_root_type_map;

    protected $global_permissions;

    protected $role_permission_cache;

    public function __construct(
        ActivityServiceInterface $activity_service,
        AggregateRootTypeMap $aggregate_root_type_map,
        array $access_config
    ) {
        $this->access_config = $access_config;
        $this->activity_service = $activity_service;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
    }

    public function getRolePermissions($role_id)
    {
        if (in_array($role_id, [ AclService::ROLE_ADMIN, AclService::ROLE_NON_PRIV ])) {
            throw new RuntimeError(
                'Role "' . $role_id . '" is an internal role and not explicitely handled by the ' .
                'permission provider. Please use only configured roles from access_control.xml.'
            );
        }

        if (!isset($this->role_permission_cache[$role_id])) {
            if (!array_key_exists($role_id, $this->access_config['roles'])) {
                throw new RuntimeError('Role "' . $role_id . '" not configured in access_control.xml.');
            }
            $role_config = $this->access_config['roles'][$role_id];
            $permissions_map = new PermissionListMap();
            foreach ($role_config['acl'] as $scope => $acl_rules) {
                $permission_list = $this->evaluateRules($this->expandScopeWildcard($acl_rules));
                if ($permission_list) {
                    $permissions_map->setItem($scope, $permission_list);
                } else {
                    // no specific permissions found ...
                }
            }
            $this->role_permission_cache[$role_id] = $permissions_map;
        }

        return $this->role_permission_cache[$role_id];
    }

    public function getGlobalPermissions()
    {
        if (!$this->global_permissions) {
            $this->global_permissions = $this->loadGlobalPermissions();
        }

        return $this->global_permissions;
    }

    protected function loadGlobalPermissions()
    {
        $permissions_map = new PermissionListMap();

        foreach ($this->activity_service->getContainers() as $scope => $container) {
            $permissions_map->setItem($scope, $this->buildDefaultPermissions($container));
        }

        return $permissions_map;
    }

    protected function buildDefaultPermissions(ActivityContainer $container)
    {
        $container_permissions = new PermissionList();
        $is_workflow_scope = preg_match(self::WORKFLOW_SCOPE_REGEXP, $container->getScope(), $matches);

        foreach ($container->getActivityMap() as $activity) {
            $permission_data = [ 'name' => $activity->getName(), 'access_scope' => $container->getScope() ];
            if ($is_workflow_scope) {
                $permission_data['type'] = 'workflow';
                $container_permissions->addItem(new Permission($permission_data));
            } else {
                $permission_data['type'] = 'activity';
                $container_permissions->addItem(new Permission($permission_data));
            }
        }

        if ($is_workflow_scope) {
            // @todo might not want to add attribute credentials to steps that are not interactive.
            // would save us "unused" credentials and some bytes within the acl ...
            $entity_type_prefix = $matches[1];
            foreach ($this->aggregate_root_type_map as $aggregate_root_type) {
                if ($aggregate_root_type->getPrefix() !== $entity_type_prefix) {
                    continue;
                }

                foreach ($aggregate_root_type->getAttributes() as $attribute) {
                    $read_permission = new Permission(
                        [
                            'name' => $attribute->getName() . ':read',
                            'type' => 'attribute',
                            'access_scope' => $container->getScope()
                        ]
                    );
                    $write_permission = new Permission(
                        [
                            'name' => $attribute->getName() . ':write',
                            'type' => 'attribute',
                            'access_scope' => $container->getScope()
                        ]
                    );

                    $container_permissions->addItem($read_permission);
                    $container_permissions->addItem($write_permission);
                }
            }
        }

        return $container_permissions;
    }

    protected function expandScopeWildcard(array $acl_rules)
    {
        $expanded_scope_rules = [];

        foreach ($acl_rules as $acl_rule) {
            $permissions_map = [];

            if (preg_match('/\*$/', $acl_rule['scope'])) {
                foreach ($this->getGlobalPermissions() as $scope => $permissions) {
                    $regex_escaped_scope = str_replace('.', '\.', $acl_rule['scope']);
                    $scope_pattern = str_replace('*', '[\w_\d]+', '/'.$regex_escaped_scope.'/');

                    if (preg_match($scope_pattern, $scope)) {
                        $expanded_rule = $acl_rule;
                        $expanded_rule['scope'] = $scope;
                        $expanded_scope_rules[] = $expanded_rule;
                    }
                }
            } elseif ($rule_permissions = $this->getGlobalPermissions()->getItem($acl_rule['scope'])) {
                $expanded_scope_rules[] = $acl_rule;
            } else {
                // @todo log/exception: configured permission scope does not exist.
            }
        }

        return $expanded_scope_rules;
    }

    protected function evaluateRules(array $rules)
    {
        $affected_permissions = new PermissionList();

        foreach ($rules as $acl_rule) {
            $mapped_permissions = new PermissionList();
            switch ($acl_rule['type']) {
                case '*':
                    $mapped_permissions = $this->evaluateWildcardRule($acl_rule);
                    break;

                case 'activity':
                    $mapped_permissions = $this->evaluateActivityRule($acl_rule);
                    break;

                case 'plugin':
                    $mapped_permissions = $this->evaluateWorkflowRule($acl_rule);
                    break;

                case 'attribute':
                    $mapped_permissions = $this->evaluateAttributeRule($acl_rule);
                    break;

                default:
                    throw new RuntimeError("Invalid credential type given: " . $acl_rule['type']);
            }
            foreach ($mapped_permissions as $mapped_permission) {
                $affected_permissions->addItem($mapped_permission);
            }
        }

        return $affected_permissions;
    }

    protected function evaluateWildcardRule(array $rule)
    {
        $scope_permissions = $this->getGlobalPermissions()->getItem($rule['scope']);
        if (!$scope_permissions) {
            // @todo log/exception: configured permission scope does not exist.
            return false;
        }

        $rule_permissions = new PermissionList();
        foreach ($scope_permissions as $permission) {
            $permission_data = $scope_permission->toArray();
            $permission_data['access_type'] = $rule['access'];
            $permission_data['expression'] = $rule['expression'];
            $rule_permissions->addItem(new Permission($permission_data));
        }

        return $rule_permissions;
    }

    protected function evaluateWorkflowRule(array $rule)
    {
        $scope_permissions = $this->getGlobalPermissions()->getItem($rule['scope']);
        if (!$scope_permissions) {
            // @todo log/exception: configured permission scope does not exist.
            return false;
        }

        $rule_permissions = new PermissionList();
        foreach ($scope_permissions as $scope_permission) {
            if (('*' === $rule['operation'] || $scope_permission->getName() === $rule['operation'])) {
                switch ($scope_permission->getType()) {
                    case 'workflow':
                    case 'activity':
                        $permission_data = $scope_permission->toArray();
                        $permission_data['access_type'] = $rule['access'];
                        $permission_data['expression'] = $rule['expression'];
                        $rule_permissions->addItem(new Permission($permission_data));
                        break;

                    default:
                        throw new RuntimeError(
                            sprintf("Unsupported permission type %s given", $scope_permission->getType())
                        );
                }
            }
        }

        return $rule_permissions;
    }

    protected function evaluateActivityRule(array $rule)
    {
        $scope_permissions = $this->getGlobalPermissions()->getItem($rule['scope']);
        if (!$scope_permissions) {
            // @todo log/exception: configured permission scope does not exist.
            return false;
        }

        $rule_permissions = new PermissionList();
        foreach ($scope_permissions as $scope_permission) {
            if ($scope_permission->getType() === 'activity'
                && ('*' === $rule['operation'] || $scope_permission->getName() === $rule['operation'])
            ) {
                $permission_data = $scope_permission->toArray();
                $permission_data['access_type'] = $rule['access'];
                $permission_data['expression'] = $rule['expression'];
                $rule_permissions->addItem(new Permission($permission_data));
            }
        }

        return $rule_permissions;
    }

    protected function evaluateAttributeRule(array $rule)
    {
        $scope_permissions = $this->getGlobalPermissions()->getItem($rule['scope']);
        if (!$scope_permissions) {
            // @todo log/exception: configured permission scope does not exist.
            return false;
        }

        $supported_operations = [];

        // matches "name:*"
        $allow_all_operations_on_specific_attribute = preg_match(
            '/([\w_\.]+):\*$/',
            $rule['operation'],
            $specific_attribute_match
        );

        if ($allow_all_operations_on_specific_attribute) {
            $supported_operations[] = $specific_attribute_match[1].':read';
            $supported_operations[] = $specific_attribute_match[1].':write';
        } else {
            $supported_operations[] = $rule['operation'];
        }

        // matches "*:*" OR "*"
        $allow_all_operations_on_all_attributes = ('*:*' === $rule['operation']) || ('*' === $rule['operation']);

        // matches "*" OR "*:*" OR "name:*"
        $allow_all_operations = $allow_all_operations_on_all_attributes
            || $allow_all_operations_on_specific_attribute;

        // matches "*:read" OR "*:write"
        $match_all_attributes_with_specific_operation = preg_match(
            '/(\*):(read|write)/',
            $rule['operation'],
            $specific_operation_match
        );

        $match_all_attributes = $allow_all_operations_on_all_attributes
            || $match_all_attributes_with_specific_operation;

        $rule_permissions = new PermissionList();

        // match each concrete permission against being a attribute permission and matching the rules
        foreach ($scope_permissions as $scope_permission) {
            if ($scope_permission->getType() !== 'attribute') {
                continue;
            }

            // INPUT: "*" or "*:*" or "name:*" or "*:read" or "name:write" (from allow/deny node in access_control.xml)
            $permission_name = $scope_permission->getName();

            // matches "*:read" or "*:write"
            $allow_specific_operation_on_all_attributes = $match_all_attributes
                && !$allow_all_operations
                && preg_match('/' . preg_quote($specific_operation_match[2]) . '$/', $permission_name);

            // matches "name:read" OR "name:write" OR "name:*"
            // (as that was expanded above into "name:read" and "name:write")
            $allow_specific_operation_on_specific_attribute = in_array($permission_name, $supported_operations);

            if ($allow_all_operations_on_all_attributes
                || $allow_specific_operation_on_all_attributes
                || $allow_specific_operation_on_specific_attribute
            ) {
                $permission_data = $scope_permission->toArray();
                $permission_data['access_type'] = $rule['access'];
                $permission_data['expression'] = $rule['expression'];
                $rule_permissions->addItem(new Permission($permission_data));
            }
        }

        return $rule_permissions;
    }
}
