<?php

namespace Honeybee\Infrastructure\Security\Auth;

interface PasswordHandlerInterface
{
       public function hash($password);

       public function verify($password, $challenge);
}
