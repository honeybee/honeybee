<?php

namespace Honeybee\Infrastructure\Security\Auth;

/**
 * The AuthResponseInterface specifies how authentication attempts shall be answered by AuthServiceInterfaces.
 */
interface AuthResponseInterface
{
    public function getMessage();

    public function getErrors();

    public function getState();

    public function getAttributes();

    public function getAttribute($name, $default = null);
}
