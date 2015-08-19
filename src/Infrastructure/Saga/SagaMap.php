<?php

namespace Honeybee\Infrastructure\Saga;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class SagaMap extends TypedMap implements UniqueCollectionInterface
{
    public function getBySubject(SagaSubjectInterface $subject)
    {
        $saga_name = $subject->getSagaName();

        if (!$this->hasKey($saga_name)) {
            throw new RuntimeError('Unable to find state-machine for name: ' . $saga_name);
        }

        return $this->getItem($saga_name);
    }

    protected function getItemImplementor()
    {
        return SagaInterface::CLASS;
    }
}
