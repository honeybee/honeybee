<?php

namespace Honeybee;

/**
 * Represents a user in an application. The user may have permissions etc.
 */
interface UserInterface
{
    /**
     * Id of role the user has in role based applications.
     *
     * @return string
     */
    public function getRoleId();

    /**
     * Checks whether user is allowed to access a resource or has the privilege
     * for certain operations on the given resource or in general.
     *
     * @param mixed $resource
     * @param mixed $privilege
     *
     * @return boolean true if permitted, false if not allowed
     */
    public function isAllowed($resource = null, $privilege = null);
}
