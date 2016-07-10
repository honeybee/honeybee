<?php

namespace Honeybee;

use Trellis\Entity\Entity as BaseEntity;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Honeybee\Common\ScopeKeyInterface;

abstract class Entity extends BaseEntity implements ResourceInterface, ScopeKeyInterface, EntityInterface
{
    const SCOPE_KEY_SEPARATOR = '.';

    public function getIdentifier()
    {
        return $this->get('identifier');
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
        $parent_attribute = $this->getEntityType()->getParentAttribute();
        $scope_key_pieces = [];

        if ($parent_attribute) {
            $scope_key_pieces[] = $this->getRoot()->getEntityType()->getScopeKey();
            if ($workflow_state = $this->getRoot()->getWorkflowState()) {
                $scope_key_pieces[] = $workflow_state;
            }
            $scope_key_pieces[] = $parent_attribute->getPath();
            $scope_key_pieces[] = $this->getEntityType()->getPrefix();
        } else {
            $scope_key_pieces[] = $this->getEntityType()->getScopeKey();
            if ($workflow_state = $this->getWorkflowState()) {
                $scope_key_pieces[] = $workflow_state;
            }
        }

        return implode(self::SCOPE_KEY_SEPARATOR, $scope_key_pieces);
    }

    public function createMirrorFrom(EntityInterface $entity)
    {
        return $this->getEntityType()->createMirroredEntity($entity, $this);
    }

    public function __toString()
    {
        return $this->getIdentifier();
    }
}
