<?php

namespace Honeybee\Infrastructure\Security\Acl\Permission;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Security\Acl\AclService;
use Honeybee\Infrastructure\Security\Acl\Permission\PermissionListMap;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Ui\Activity\ActivityContainer;
use Honeybee\Ui\Activity\ActivityServiceInterface;
use Trellis\Common\Configurable;

class PermissionService extends Configurable implements PermissionServiceInterface
{
    const WORKFLOW_SCOPE_REGEXP = '/(.*)\.resource\.workflow\./';

    protected $activity_service;

    protected $access_config;

    protected $aggregate_root_type_map;

    protected $global_permissions;

    protected $role_permission_cache;

    protected $additional_permissions;

    public function __construct(
        ActivityServiceInterface $activity_service,
        AggregateRootTypeMap $aggregate_root_type_map,
        array $access_config,
        PermissionListMap $additional_permissions
    ) {
        $this->access_config = $access_config;
        $this->activity_service = $activity_service;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->additional_permissions = $additional_permissions;
    }

    public function getRolePermissions($role_id)
    {
        if (in_array($role_id, [ AclService::ROLE_FULL_PRIV, AclService::ROLE_NON_PRIV ])) {
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
                $actual_acl_rules = $this->expandScopeWildcard($acl_rules);
error_log(__METHOD__ . ' scope ' . $scope . ' – ' . count($acl_rules) . ' rules expanded to ' . count($actual_acl_rules));
                $permission_list = $this->evaluateRules($actual_acl_rules);
                if ($permission_list) {
                    $permissions_map->setItem($scope, $permission_list);
                } else {
                    // no specific permissions found ...
                }
            }
// error_log(var_export($permissions_map->toArray(), true));
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

        $unused_additional_scopes = [];
        foreach ($this->activity_service->getContainers() as $scope => $container) {
            $permissions = $this->buildDefaultPermissions($container);
            if ($this->additional_permissions->hasKey($scope)) {
                foreach ($this->additional_permissions as $sc => $perms) {
                    foreach ($perms as $perm) {
                        $permissions->addItem($perm);
                    }
                }
            } else {
                $unused_additional_scopes[] = $scope;
            }

            $permissions_map->setItem($scope, $permissions);
        }

        foreach ($unused_additional_scopes as $scope) {
            $perms = $this->additional_permissions->getItem($scope);
            if ($perms) {
                $permissions_map->setItem($scope, $this->additional_permissions->getItem($scope));
            }
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

        $global_permissions = $this->getGlobalPermissions();
        $scopes = $global_permissions->getKeys();

        foreach ($acl_rules as $acl_rule) {
            $permissions_map = [];

            if (preg_match('/\*$/', $acl_rule['scope'])) {
                foreach ($scopes as $scope) {
                    $regex_escaped_scope = str_replace('.', '\.', $acl_rule['scope']);
                    $scope_pattern = str_replace('*', '[\w_\d]+', '/'.$regex_escaped_scope.'/');

                    if (preg_match($scope_pattern, $scope)) {
                        $expanded_rule = $acl_rule;
                        $expanded_rule['scope'] = $scope;
                        $expanded_scope_rules[] = $expanded_rule;
                    }
                }
            } elseif ($rule_permissions = $global_permissions->getItem($acl_rule['scope'])) {
                $expanded_scope_rules[] = $acl_rule;
            } else {
                error_log('ADDING UNKNOWN SCOPE ' . $acl_rule['scope']);
                $expanded_scope_rules[] = $acl_rule;
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
                    $affected_permissions->append($this->evaluateActivityRule($acl_rule));
                    break;

                case 'plugin':
                    $affected_permissions->append($this->evaluateWorkflowRule($acl_rule));
                    break;

                case 'attribute':
                    $affected_permissions->append($this->evaluateAttributeRule($acl_rule));
                    break;

                case 'method':
                    $affected_permissions->append($this->evaluateMethodRule($acl_rule));
                    break;

                default:
                    throw new RuntimeError('Invalid credential type given: "' . $acl_rule['type'] .
                        '". Expected one of: "*", "activity", "plugin", "attribute" or "method".');
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
            $permission_data = $permission->toArray();
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

    protected function evaluateMethodRule(array $rule)
    {
        if ($rule['type'] !== 'method') {
            throw new RuntimeError('Only accepting ACL rule data where "type" is "method".');
        }

        $rule_permissions = new PermissionList();

        $permission_tpl = [
            'type' => 'method',
            'name' => $rule['operation'],
            'access_scope' => $rule['scope'],
            'access_type' => $rule['access'],
            'expression' => $rule['expression'],
        ];

        $default_methods = $this->getDefaultMethodsSupported();

        if ('*' === $rule['operation']) {
            foreach ($default_methods as $method) {
                $permission_data = $permission_tpl;
                $permission_data['name'] = $method;
                $permission_data['operation'] = $method;
                error_log('* => ' . var_export($permission_data, true));
                $rule_permissions->addItem(new Permission($permission_data));
            }
        } else {
            $permission_data = $permission_tpl;
            error_log('… => ' . var_export($permission_data, true));
            $rule_permissions->addItem(new Permission($permission_data));
        }

        return $rule_permissions;
    }

    protected function getDefaultMethodsSupported()
    {
        return ['read', 'write'];
    }
}
