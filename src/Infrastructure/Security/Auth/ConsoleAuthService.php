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
        $user_env_var = getenv($this->config->get('user_env_var', 'APP_USER'));
        $system_username = ($user_env_var === false) ? get_current_user() : $user_env_var;

        $role_map = $this->config->get('role_map');

        if (isset($role_map[$system_username])) {
            return new AuthResponse(
                AuthResponse::STATE_AUTHORIZED,
                'authenticaton success',
                [
                    'login' => $system_username,
                    'acl_role' => $role_map[$system_username]
                ]
            );
        }

        return new AuthResponse(
            AuthResponse::STATE_UNAUTHORIZED,
            'authentication failed',
            [],
            [ 'Unable to map system user to honeybee role.' ]
        );
    }
}
