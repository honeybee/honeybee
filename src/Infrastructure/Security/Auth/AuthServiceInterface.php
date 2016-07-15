<?php

namespace Honeybee\Infrastructure\Security\Auth;

/**
 * The AuthServiceInterface specifies how authentication shall be exposed to consuming components
 * inside the Auth module.
 */
interface AuthServiceInterface
{
    public function getTypeKey();

    public function authenticate($username, $password, $options = []);
}
