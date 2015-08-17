<?php

namespace Honeybee\Infrastructure\Mail;

use InvalidArgumentException;

/**
 * Exception to throw when settings values on a Message or
 * sending a Message does not work because of invalid
 * settings or email addresses.
 */
class MessageConfigurationException extends InvalidArgumentException
{

}
