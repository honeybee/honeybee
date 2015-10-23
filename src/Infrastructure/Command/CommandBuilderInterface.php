<?php

namespace Honeybee\Infrastructure\Command;

interface CommandBuilderInterface
{
    /**
     * @return Result Either Success with a CommandInterface inside or an Error with a list of errors.
     */
    public function build();
}
