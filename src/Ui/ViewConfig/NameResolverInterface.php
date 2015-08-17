<?php

namespace Honeybee\Ui\ViewConfig;

interface NameResolverInterface
{
    /**
     * Resolves a name for the given subject. That name may be used to
     * look up e.g. renderer configs or similar for that subject.
     *
     * @param mixed $subject subject to resolve a name for
     *
     * @return string name for that subject
     */
    public function resolve($subject);
}
