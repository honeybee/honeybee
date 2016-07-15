<?php

namespace Honeybee;

use Trellis\Entity\EntityInterface as TrellisEntityInterface;

interface EntityInterface extends TrellisEntityInterface
{
    public function createMirrorFrom(EntityInterface $entity);
}
