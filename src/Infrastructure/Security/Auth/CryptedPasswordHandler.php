<?php

namespace Honeybee\Infrastructure\Security\Auth;

class CryptedPasswordHandler implements PasswordHandlerInterface
{
    public function hash($password)
    {
        $options = [
            'cost' => 11,
            'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)
        ];

        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function verify($password, $challenge)
    {
        return password_verify($password, $challenge);
    }
}
