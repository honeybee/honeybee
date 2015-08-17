<?php

namespace Honeybee\Model\Event;

interface EmbeddedEntityEventInterface
{
    public function getEmbeddedEntityIdentifier();

    public function getEmbeddedEntityType();

    public function getParentAttributeName();
}
