<?php

namespace Honeybee\Infrastructure\Command;

use Shrink0r\Monatic\Result;

interface CommandBuilderInterface
{
    /**
     * @return Result Either Success with a CommandInterface inside or an Error with a list of errors.
     */
    public function build();
}
