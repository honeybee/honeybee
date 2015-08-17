<?php

namespace Honeybee\Infrastructure\Security\Auth;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\SystemAccount\User\Projection\Standard\UserType;
use Honeybee\SystemAccount\User\Projection\UserQueryService;

/**
 * The StandardAuthProvider provides authentication against account information coming from the User module.
 */
class StandardAuthService implements AuthServiceInterface
{
    const ACTIVE_STATE = 'active';

    const TYPE_KEY = 'standard-auth';

    protected $config;

    protected $password_handler;

    protected $user_type;

    protected $query_service_map;

    public function __construct(
        ConfigInterface $config,
        QueryServiceMap $query_service_map,
        CryptedPasswordHandler $password_handler
    ) {
        $this->config = $config;
        $this->password_handler = $password_handler;
        $this->query_service_map = $query_service_map;
    }

    public function getTypeKey()
    {
        return static::TYPE_KEY;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function authenticate($username, $password, $options = array()) // @codingStandardsIgnoreEnd
    {
        $query_result = $this->getQueryService()->find(
            new Query(
                new CriteriaList,
                new CriteriaList([ new AttributeCriteria('username', $username) ]),
                new CriteriaList,
                0,
                1
            )
        );

        $user = null;
        if (1 === $query_result->getTotalCount()) {
            $user = $query_result->getFirstResult();
        } else {
            return new AuthResponse(AuthResponse::STATE_UNAUTHORIZED, "authentication failed");
        }
        /*if ($user->getWorkflowState() !== $this->config->get('active_state', self::ACTIVE_STATE)) {
            return new AuthResponse(
                AuthResponse::STATE_UNAUTHORIZED,
                "user inactive"
            );
        }*/

        if ($this->password_handler->verify($password, $user->getPasswordHash())) {
            return new AuthResponse(
                AuthResponse::STATE_AUTHORIZED,
                "authenticaton success",
                [
                    'login' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'acl_role' => $user->getRole(),
                    'name' => $user->getFirstname() . ' ' . $user->getLastname(),
                    'identifier' => $user->getIdentifier()
                ]
            );
        }

        return new AuthResponse(AuthResponse::STATE_UNAUTHORIZED, "authentication failed");
    }

    protected function getQueryService()
    {
        $query_service_key = $this->config->get('query_service', 'honeybee.system_account.user::query_service');
        if (!$this->query_service_map->hasKey($query_service_key)) {
            throw new RuntimeError('Unable to find QueryService for key: ' . $query_service_key);
        }

        return $this->query_service_map->getItem($query_service_key);
    }
}
