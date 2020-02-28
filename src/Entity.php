<?php

namespace Honeybee;

use Trellis\Runtime\Entity\Entity as BaseEntity;
use Trellis\Runtime\ValueHolder\ValueChangedEvent;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Honeybee\Common\ScopeKeyInterface;

abstract class Entity extends BaseEntity implements ResourceInterface, ScopeKeyInterface, EntityInterface
{
    const SCOPE_KEY_SEPARATOR = '.';

    public function onValueChanged(ValueChangedEvent $event)
    {
        // skip value changed events, that bubbled up from embedded entities
        if (!$event->getEmbeddedEvent()) {
            $this->changes->push($event);
        }
    }

    public function getIdentifier()
    {
        return $this->getValue('identifier');
    }

    /**
     * Return the resource id that is used to represent this entity in the context of ACL assertions.
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->getScopeKey();
    }

    /**
     * Return the 'honeybee' scope that adresses the current entity.
     *
     * @return string
     */
    public function getScopeKey()
    {
        $parent_attribute = $this->getType()->getParentAttribute();
        $scope_key_pieces = [];

        if ($parent_attribute) {
            $scope_key_pieces[] = $this->getRoot()->getType()->getScopeKey();
            if ($workflow_state = $this->getRoot()->getWorkflowState()) {
                $scope_key_pieces[] = $workflow_state;
            }
            $scope_key_pieces[] = $parent_attribute->getPath();
            $scope_key_pieces[] = $this->getType()->getPrefix();
        } else {
            $scope_key_pieces[] = $this->getType()->getScopeKey();
            if ($workflow_state = $this->getWorkflowState()) {
                $scope_key_pieces[] = $workflow_state;
            }
        }

        return implode(self::SCOPE_KEY_SEPARATOR, $scope_key_pieces);
    }

    public function createMirrorFrom(EntityInterface $entity)
    {
        return $this->getType()->createMirroredEntity($entity, $this);
    }

    public function __toString()
    {
        return $this->getIdentifier();
    }
}
