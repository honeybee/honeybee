<?php

namespace Honeybee\Model\Aggregate;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Model\Command\AggregateRootCommandInterface;
use Honeybee\Model\Command\AggregateRootTypeCommandInterface;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Event\EmbeddedEntityEventList;
use Honeybee\Model\Task\CreateAggregateRoot\AggregateRootCreatedEvent;
use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand;
use Honeybee\Model\Task\MoveAggregateRootNode\AggregateRootNodeMovedEvent;
use Honeybee\Model\Task\MoveAggregateRootNode\MoveAggregateRootNodeCommand;
use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommand;
use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\Uuid\Uuid;
use Trellis\EntityType\Attribute\Uuid\Uuidd;
use Workflux\StateMachine\StateMachineInterface;

/**
 * Base class that should expose behaviour around the core business, that a specific entity has been created for.
 */
abstract class AggregateRoot extends Entity implements AggregateRootInterface
{
    /**
     * @var StateMachineInterface $state_machine
     */
    protected $state_machine;

    /**
     * @var AggregateRootEventList $history
     */
    protected $history;

    /**
     * @var AggregateRootEventList $uncomitted_events_list
     */
    protected $uncomitted_events_list;

    public function __construct(AggregateRootTypeInterface $aggregate_root_type, StateMachineInterface $state_machine)
    {
        parent::__construct($aggregate_root_type, []);

        $this->state_machine = $state_machine;
        $this->history = new AggregateRootEventList;
        $this->uncomitted_events_list = new AggregateRootEventList;
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
     * Return a aggregate-root's uuid.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->get('uuid');
    }

    /**
     * Returns an aggregate-root's language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->get('language');
    }

    /**
     * Returns an aggregate-root's version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->get('version');
    }

    /**
     * Returns an aggregate-root's revision.
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->get('revision');
    }

    /**
     * Return the id of our parent-node, if the aggregate's data is being managed as a tree.
     *
     * @return string
     *
     * @throws RuntimeError
     */
    public function getParentNodeId()
    {
        if (!$this->getEntityType()->isActingAsTree()) {
            throw new RuntimeError('Cant return parent_node_id for a non-hierarchically managed type.');
        }

        return $this->get('parent_node_id');
    }

    /**
     * Return the name of the current workflow state.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->get('workflow_state');
    }

    /**
     * Return an array holding the current workflow parameters.
     *
     * @return array
     */
    public function getWorkflowParameters()
    {
        return $this->get('workflow_parameters');
    }

    /**
     * Mark the aggregate-root as comitted, meaning all pending changes have been persisted by the UOW.
     */
    public function markAsComitted()
    {
        $this->uncomitted_events_list = new AggregateRootEventList;
    }

    /**
     * Return a list of changes that are waiting to be persisted/comitted.
     *
     * @return AggregateRootEventList
     */
    public function getUncomittedEvents()
    {
        return $this->uncomitted_events_list;
    }

    /**
     * Return a list of events that have occured in the past.
     *
     * @return AggregateRootEventList
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Rebuild an aggregate-root's latest state based on the given audit log.
     *
     * @param AggregateRootEventList $history
     */
    public function reconstituteFrom(AggregateRootEventList $history)
    {
        Assertion::count($this->history, 0, 'Reconstituting an already initialized aggregate-root is not allowed.');
        for ($i = 0; $i < $history->getSize(); $i++) {
            $past_event = $history[$i];
            Assertion::eq($past_event->getSeqNumber(), $i, 'Unexpected seq-number given within history.');
            $this->history = $this->history->push($this->applyEvent($past_event, false));
        }
    }

    /**
     * Start a new life-cycle for the current aggregate-root.
     *
     * @param CreateAggregateRootCommand $create_command
     */
    public function create(CreateAggregateRootCommand $create_command)
    {
        $created_event = $this->processCommand(
            $create_command,
            [ 'data' => $this->createInitialData($create_command) ]
        );
        Assertion::isInstanceOf(
            AggregateRootCreatedEvent::CLASS,
            $created_event,
            'Events that reflect AR creation must descend from: ' . AggregateRootCreatedEvent::CLASS
        );

        $this->applyEvent($created_event);
    }

    /**
     * Modify the state of the current aggregate-root.
     *
     * @param ModifyAggregateRootCommand $modify_command
     */
    public function modify(ModifyAggregateRootCommand $modify_command)
    {
        $this->guardCommandPreConditions($modify_command);
        $modified_event = $this->processCommand(
            $modify_command,
            [ 'data' => $modify_command->getValues() ]
        );
        Assertion::isInstanceOf(
            AggregateRootModifiedEvent::CLASS,
            $modified_event,
            'Events that reflect AR mutation must descend from: ' . AggregateRootModifiedEvent::CLASS
        );

        $this->applyEvent($modified_event);
    }

    /**
     * Transition to the next workflow state (hence next state of the statemachine based on the command paylaod).
     *
     * @param ProceedWorkflowCommand $workflow_command
     */
    public function proceedWorkflow(ProceedWorkflowCommand $workflow_command)
    {
        $this->guardCommandPreConditions($workflow_command);
        Assertion::eq($workflow_command->getCurrentStateName(), $this->getWorkflowState(), sprintf(
            "Unexpected workflow state. Expected: '%s', given '%s'.",
            $this->getWorkflowState(),
            $workflow_command->getCurrentStateName()
        ));

        $workflow_subject = new WorkflowSubject($this->state_machine->getName(), $this);
        $this->state_machine->execute($workflow_subject, $workflow_command->getEventName());

        $proceeded_event = $this->processCommand(
            $workflow_command,
            [
                'data' => [
                    'workflow_state' => $workflow_subject->getCurrentStateName(),
                    'workflow_parameters' => $workflow_subject->getWorkflowParameters()
                ]
            ]
        );
        Assertion::isInstanceOf(
            WorkflowProceededEvent::CLASS,
            $proceeded_event,
            'Events that reflect  workflow transitions must descend from: ' . WorkflowProceededEvent::CLASS
        );

        $this->applyEvent($proceeded_event);
    }

    /**
     * Transition to the next workflow state (hence next state of the statemachine based on the command paylaod).
     *
     * @param MoveAggregateRootNodeCommand $move_node_command
     *
     * @throws RuntimeError
     */
    public function moveNode(MoveAggregateRootNodeCommand $move_node_command)
    {
        $this->guardCommandPreConditions($move_node_command);
        $node_moved_event = $this->processCommand(
            $move_node_command,
            [ 'data' => [ 'parent_node_id' => $move_node_command->getParentNodeId() ] ]
        );
        Assertion::isInstanceOf(
            AggregateRootNodeMovedEvent::CLASS,
            $node_moved_event,
            'Events that reflect  nodes being moved must descend from: ' . AggregateRootNodeMovedEvent::CLASS
        );

        $this->applyEvent($node_moved_event);
    }

    /**
     * Create the data used to initialize a new aggregate-root.
     *
     * @param CreateAggregateRootCommand $create_command
     *
     * @return array
     */
    protected function createInitialData(CreateAggregateRootCommand $create_command)
    {
        $entity_type = $this->getEntityType();
        $create_data = $create_command->getValues();

        $value_or_default = function ($key, $default) use ($create_data) {
            return isset($create_data[$key]) ? $create_data[$key] : $default;
        };
        $uuid = $value_or_default('uuid', Uuid::generate()->toNative());
        $language = $value_or_default('language', $entity_type->getOption('default_lang', 'de_DE'));
        $version = $value_or_default('version', 1);

        return array_merge(
            $create_data,
            [
                'identifier' => sprintf('%s-%s-%s-%s', $entity_type->getPrefix(), $uuid, $language, $version),
                'uuid' => $uuid,
                'language' => $language,
                'version' => $version,
                'workflow_state' => $this->state_machine->getInitialState()->getName()
            ]
        );
    }

    /**
     * Check if the given command conflicts with any events that have occured since it was issued.
     *
     * @param AggregateRootCommandInterface $command
     *
     * @throws RuntimeError
     */
    protected function guardCommandPreConditions(AggregateRootCommandInterface $command)
    {
        Assertion::notEmpty($this->getHistory(), 'Cant send commands to a not yet created AR.');
        Assertion::greaterOrEqualThan(
            $this->getRevision(),
            $command->getKnownRevision(),
            'The current head revision may not be smaller than the commands given known-revision.'
        );

        $conflicting_events = [];
        if ($this->getHistory()->getLast()->getSeqNumber() > $command->getKnownRevision()) {
            $conflicting_events = $this->getHistory()->reverse()->filter(
                function (AggregateRootEventInterface $event) use ($command) {
                    return $event->getSeqNumber() > $command->getKnownRevision()
                        && $command->conflictsWith($event);
                }
            );
        }
        Assertion::count($conflicting_events, 0, 'Conflict with concurrent operation detected, command cancelled.');
    }

    /**
     * Process the given command, hence build the corresponding aggregate-root-event.
     *
     * @param AggregateRootTypeCommandInterface $command
     * @param array $custom_event_state
     *
     * @return AggregateRootEventInterface
     *
     * @throws RuntimeError
     */
    protected function processCommand(AggregateRootTypeCommandInterface $command, array $custom_event_state = [])
    {
        $ar_identifier = null;
        if ($command instanceof AggregateRootCommandInterface) {
            $ar_identifier = $command->getAggregateRootIdentifier();
        } else {
            $payload = isset($custom_event_state['data']) ? $custom_event_state['data'] : $custom_event_state;
            $ar_identifier = isset($$payload['aggregate_root_identifier'])
                ? $$payload['aggregate_root_identifier']
                : null;
        }
        Assertion::notEmpty(
            $ar_identifier,
            'Missing required "aggregate_root_identifier" attribute for building domain-event.'
        );

        $embedded_entity_events = new EmbeddedEntityEventList;
        foreach ($command->getEmbeddedEntityCommands() as $embedded_command) {
            $embedded_entity_events->push($this->processChildCommand($embedded_command));
        }

        $event_class = $command->getEventClass();
        return new $event_class(array_merge($custom_event_state, [
            'aggregate_root_type' => $command->getAggregateRootType(),
            'aggregate_root_identifier' => $ar_identifier,
            'metadata' => $command->getMetadata(),
            'seq_number' => $this->getRevision() + 1,
            'uuid' => $command->getUuid(),
            'embedded_entity_events' => $embedded_entity_events
        ]));
    }

    /**
     * Process the given aggregate-command, hence build the corresponding aggregate-event.
     *
     * @param CommandInterface $command
     * @param array $custom_event_state
     *
     * @return \Honeybee\Model\Event\EmbeddedEntityEventInterface;
     */
    protected function processChildCommand(CommandInterface $command, array $custom_event_state = [])
    {
        $event_class = $command->getEventClass();
        $attribute_name = $command->getParentAttributeName();

        $event_state = [
            'parent_attribute_name' => $attribute_name,
            'embedded_entity_type' => $command->getEmbeddedEntityType()
        ];

        if ($command instanceof RemoveEmbeddedEntityCommand) {
            $event_state['embedded_entity_identifier'] = $command->getEmbeddedEntityIdentifier();
        } elseif ($command instanceof AddEmbeddedEntityCommand) {
            $create_data = $command->getValues();
            if (!isset($create_data['identifier'])) {
                $create_data['identifier'] = Uuid::generate();
            }
            $event_state = array_merge(
                $event_state,
                [
                    'data' => $create_data,
                    'position' => $command->getPosition(),
                    'embedded_entity_identifier' => $create_data['identifier']
                ]
            );
        } elseif ($command instanceof ModifyEmbeddedEntityCommand) {
            $event_state = array_merge(
                $event_state,
                [
                    'data' => $command->getValues(),
                    'position' => $command->getPosition(),
                    'embedded_entity_identifier' => $command->getEmbeddedEntityIdentifier()
                ]
            );
        }
        $embedded_entity_events = new EmbeddedEntityEventList;
        foreach ($command->getEmbeddedEntityCommands() as $embedded_command) {
            $embedded_entity_events->push($this->processChildCommand($embedded_command));
        }
        $event_state['embedded_entity_events'] = $embedded_entity_events;

        return new $event_class($event_state);
    }

    /**
     * Takes an event and applies the resulting state change to the aggregate-root's internal state.
     *
     * @param AggregateRootEventInterface $event
     * @param bool $auto_commit Whether to directly add the given event to the uncomitted-events list.
     *
     * @return AggregateRootEventInterface Event that is acutally applied and comitted or false if the AR is invalid.
     */
    protected function applyEvent(AggregateRootEventInterface $event, $auto_commit = true)
    {
        $this->value_map = $this->value_map->withItems($event->getData());
        $embedded_entity_events = new EmbeddedEntityEventList;
        foreach ($event->getEmbeddedEntityEvents() as $embedded_entity_event) {
            $embedded_entity_events->push($this->applyEmbeddedEntityEvent($embedded_entity_event));
        }

        $source_event = null;
        if ($auto_commit) {
            $recorded_changes = $this->getRecordedChanges();
            if (!empty($recorded_changes) || !$embedded_entity_events->isEmpty()) {
                $source_event = $event->createCopyWith(
                    [ 'data' => $recorded_changes, 'embedded_entity_events' => $embedded_entity_events ]
                );
                $this->uncomitted_events_list->push($source_event);
                $this->history->push($source_event);
            }
        } else {
            $source_event = $event;
        }

        if ($source_event) {
            $this->setValue('revision', $source_event->getSeqNumber());
            $this->markClean();
        } else {
            $notice = 'Applied event %s for %s did not trigger any state changes, so it is being dropped ...';
            error_log(sprintf($notice, $event, $this));
        }

        return $source_event;
    }

    /**
     * Helper method, that makes it easier to apply a command in order to achieve a state transition,
     * that is "just" based on classical attribute changes.
     *
     * @param AggregateRootCommandInterface $command
     * @param array $changing_attributes
     */
    protected function modifyAttributesThrough(AggregateRootCommandInterface $command, array $changing_attributes)
    {
        $this->guardCommandPreConditions($command);
        $this->applyEvent($this->processCommand($command, [ 'data' => $changing_attributes ]));
    }
}
