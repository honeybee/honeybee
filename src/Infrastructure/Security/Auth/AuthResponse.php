<?php

namespace Honeybee\Infrastructure\Security\Auth;

/**
 * The AuthResponse class is the default implementation of the AuthResponseInterface interface.
 * It provides data representing the result of an authentication attempt.
 */
class AuthResponse implements AuthResponseInterface
{
    const STATE_AUTHORIZED = true;

    const STATE_UNAUTHORIZED = false;

    const STATE_ERROR = -1;

    protected $message;

    protected $errors;

    protected $state;

    protected $attributes;

    public function __construct($state, $message, $attributes = array(), $errors = array())
    {
        $this->state = $state;
        $this->errors = $errors;
        $this->message = $message;
        $this->attributes = $attributes;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }
}
