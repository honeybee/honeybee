<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot;

use Assert\Assertion;
use Honeybee\Model\Command\AggregateRootCommand;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;

abstract class ModifyAggregateRootCommand extends AggregateRootCommand
{
    protected $values;

    public function getAffectedAttributeNames()
    {
        return array_keys($this->values);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function conflictsWith(AggregateRootEventInterface $event, array &$conflicting_changes = [])
    {
        if ($event->getAggregateRootIdentifier() !== $this->getAggregateRootIdentifier()) {
            return false;
        }

        $conflict_detected = false;

        if ($event instanceof WorkflowProceededEvent) {
            // workflow events always conflict as there is no way to know,
            // that the employed modification is still valid within the new workflow-state.
            $conflict_detected = true;
        } elseif ($event instanceof AggregateRootModifiedEvent) {
            // concurrent modifications can be resolved,
            // if they dont alter the same attributes or employ equal modifications.
            $event_changes = $event->getData();
            foreach ($this->getValues() as $attribute_name => $attribute_value) {
                if (array_key_exists($attribute_name, $event_changes)
                    && $event_changes[$attribute_name] != $this->values[$attribute_name]
                ) {
                    $conflicting_changes[$attribute_name] = $attribute_value;
                    $conflict_detected = true;
                }
            }
        }

        return $conflict_detected;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::isArray($this->values);
    }
}
