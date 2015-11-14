<?php

namespace Honeybee\Infrastructure\Security\Auth;

use Honeybee\Infrastructure\Config\ConfigInterface;

/**
 * The ConsoleAuthProvider provides authentication for cli calls.
 */
class ConsoleAuthService implements AuthServiceInterface
{
    const TYPE_KEY = 'console-auth';

    private $accounts;

    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getTypeKey()
    {
        return static::TYPE_KEY;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function authenticate($username, $password, $options = []) // @codingStandardsIgnoreEnd
    {
        $system_username = get_current_user();
        $role_map = $this->config->get('role_map');

        if (isset($role_map[$system_username])) {
            return new AuthResponse(
                AuthResponse::STATE_AUTHORIZED,
                "authenticaton success",
                [ 'acl_role' => $role_map[$system_username] ]
            );
        }

        return new AuthResponse(
            AuthResponse::STATE_UNAUTHORIZED,
            "authentication failed",
            [],
            [ 'Unable to map system user to honeybee role.' ]
        );
    }
}
