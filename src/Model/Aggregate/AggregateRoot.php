<?php

namespace Honeybee\Model\Aggregate;

use Honeybee\Common\Error\AggregateRoot\AggregateRootError;
use Honeybee\Common\Error\AggregateRoot\CommandRevisionError;
use Honeybee\Common\Error\AggregateRoot\HistoryConflictError;
use Honeybee\Common\Error\AggregateRoot\HistoryEmptyError;
use Honeybee\Common\Error\AggregateRoot\InvalidSequenceNumberError;
use Honeybee\Common\Error\AggregateRoot\InvalidStateError;
use Honeybee\Common\Error\AggregateRoot\MissingIdentifierError;
use Honeybee\Common\Error\AggregateRoot\ReconstitutionError;
use Honeybee\Common\Error\AggregateRoot\UnsupportedEventTypeError;
use Honeybee\Common\Error\AggregateRoot\WorkflowStateMismatchError;
use Honeybee\Common\Error\AggregateRoot\WrongIdentifierError;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
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
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\Uuid\UuidAttribute;
use Workflux\StateMachine\StateMachineInterface;

/**
 * Base class that should expose behaviour around the core business, that a specific entity has been created for.
 */
abstract class AggregateRoot extends Entity implements AggregateRootInterface
{
    /**
     * @var AggregateRootEventList $history
     */
    protected $history;

    /**
     * @var AggregateRootEventList $uncomitted_events_list
     */
    protected $uncomitted_events_list;

    /**
     * @todo Find a way of getting the state machine here, without "hacking" the Trellis ctor.
     * Maybe we dont want to extend but to compose the generated trellis entities??
     * And maybe the problem will fix itself, once we have separate models for reading and writing.
     */
    public function __construct(AggregateRootTypeInterface $aggregate_root_type, array $data = [])
    {
        parent::__construct($aggregate_root_type, $data);

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
        return $this->getValue('uuid');
    }

    /**
     * Returns an aggregate-root's language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->getValue('language');
    }

    /**
     * Returns an aggregate-root's version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->getValue('version');
    }

    /**
     * Returns an aggregate-root's revision.
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->getValue('revision');
    }

    /**
     * Return the id of our parent-node, if the aggregate's data is being managed as a tree.
     *
     * @return string
     */
    public function getParentNodeId()
    {
        if (!$this->getType()->isActingAsTree()) {
            throw new AggregateRootError('Cant return parent_node_id for a non-hierarchically managed type.');
        }

        return $this->getValue('parent_node_id');
    }

    /**
     * Return the name of the current workflow state.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->getValue('workflow_state');
    }

    /**
     * Return an array holding the current workflow parameters.
     *
     * @return array
     */
    public function getWorkflowParameters()
    {
        return $this->getValue('workflow_parameters');
    }

    /**
     * Mark the aggregate-root as comitted, meaning all pending changes have been persisted by the UOW.
     */
    public function markAsComitted()
    {
        $this->uncomitted_events_list->clear();
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
        if (!$this->history->isEmpty()) {
            throw new ReconstitutionError('Trying to reconstitute history on an already initialized aggregate-root.');
        }

        $first = true;
        foreach ($history as $past_event) {
            if ($first) {
                $first = false;
                if (!$past_event instanceof AggregateRootCreatedEvent) {
                    throw new ReconstitutionError(
                        sprintf(
                            'The first event given within a history to reconstitute from must be by the type of "%s".' .
                            ' Instead "%s" was given for AR %s.',
                            AggregateRootCreatedEvent::CLASS,
                            get_class($past_event),
                            $past_event->getAggregateRootIdentifier()
                        )
                    );
                }
            }

            $this->history->push($this->applyEvent($past_event, false));
        }

        return $this->isValid();
    }

    /**
     * Start a new life-cycle for the current aggregate-root.
     *
     * @param CreateAggregateRootCommand $create_command
     * @param StateMachineInterface $state_machine
     */
    public function create(CreateAggregateRootCommand $create_command, StateMachineInterface $state_machine)
    {
        $initial_data = $this->createInitialData($create_command, $state_machine);

        $created_event = $this->processCommand(
            $create_command,
            [ 'aggregate_root_identifier' => $initial_data['identifier'], 'data' => $initial_data ]
        );

        if (!$created_event instanceof AggregateRootCreatedEvent) {
            throw new UnsupportedEventTypeError(
                sprintf(
                    'Corrupt event type detected. Events that reflect entity creation must descend from %s.',
                    AggregateRootCreatedEvent::CLASS
                )
            );
        }

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

        if (!$modified_event instanceof AggregateRootModifiedEvent) {
            throw new UnsupportedEventTypeError(
                sprintf(
                    'Corrupt event type detected. Events that reflect entity modification must descend from %s.',
                    AggregateRootModifiedEvent::CLASS
                )
            );
        }

        $this->applyEvent($modified_event);
    }

    /**
     * Transition to the next workflow state (next state of the state machine based on the command paylaod).
     *
     * @param ProceedWorkflowCommand $workflow_command
     * @param StateMachineInterface $state_machine
     */
    public function proceedWorkflow(ProceedWorkflowCommand $workflow_command, StateMachineInterface $state_machine)
    {
        $this->guardCommandPreConditions($workflow_command);

        if ($workflow_command->getCurrentStateName() !== $this->getWorkflowState()) {
            throw new WorkflowStateMismatchError(
                sprintf(
                    'The AR\'s(%s) current state %s does not match the given command state %s.',
                    $this,
                    $this->getWorkflowState(),
                    $workflow_command->getCurrentStateName()
                )
            );
        }

        $workflow_subject = new WorkflowSubject($state_machine->getName(), $this);
        $previous_state = $this->getWorkflowState();
        $state_machine->execute($workflow_subject, $workflow_command->getEventName());

        $workflow_data = [
            'workflow_state' => $workflow_subject->getCurrentStateName(),
            'workflow_parameters' => array_merge(
                $workflow_subject->getWorkflowParameters(),
                [
                    'previous_state' => $previous_state,
                    'workflow_event' => $workflow_command->getEventName()
                ]
            )
        ];

        $proceeded_event = $this->processCommand($workflow_command, [ 'data' => $workflow_data ]);

        if (!$proceeded_event instanceof WorkflowProceededEvent) {
            throw new UnsupportedEventTypeError(
                sprintf(
                    'Corrupt event type detected. Events that reflect workflow transitions must descend from %s.',
                    WorkflowProceededEvent::CLASS
                )
            );
        }

        $this->applyEvent($proceeded_event);
    }

    /**
     * Transition to the next workflow state (hence next state of the statemachine based on the command paylaod).
     *
     * @param ProceedWorkflowCommand $move_node_command
     */
    public function moveNode(MoveAggregateRootNodeCommand $move_node_command)
    {
        $this->guardCommandPreConditions($move_node_command);

        $node_moved_event = $this->processCommand(
            $move_node_command,
            [ 'data' => [ 'parent_node_id' => $move_node_command->getParentNodeId() ] ]
        );

        if (!$node_moved_event instanceof AggregateRootNodeMovedEvent) {
            throw new UnsupportedEventTypeError(
                sprintf(
                    'Corrupt event type detected. Events that reflect nodes being moved must descend from %s.',
                    AggregateRootNodeMovedEvent::CLASS
                )
            );
        }

        $this->applyEvent($node_moved_event);
    }

    /**
     * Create the data used to initialize a new aggregate-root.
     *
     * @param CreateAggregateRootCommand $create_command
     * @param StateMachineInterface $state_machine
     *
     * @return array
     */
    protected function createInitialData(
        CreateAggregateRootCommand $create_command,
        StateMachineInterface $state_machine
    ) {
        $type = $this->getType();
        $type_prefix = $type->getPrefix();

        $create_data = $create_command->getValues();
        $create_data[self::OBJECT_TYPE] = $type_prefix;

        $value_or_default = function ($key, $default) use ($create_data) {
            return isset($create_data[$key]) ? $create_data[$key] : $default;
        };

        $uuid = $value_or_default('uuid', $type->getAttribute('uuid')->getDefaultValue());
        $language = $value_or_default('language', $type->getAttribute('language')->getDefaultValue());
        $version = $value_or_default('version', 1);
        $identifier = sprintf('%s-%s-%s-%s', $type_prefix, $uuid, $language, $version);

        $default_attributes = $type->getDefaultAttributes();
        $non_default_attributes = $type->getAttributes()->filter(
            function (AttributeInterface $attribute) use ($default_attributes) {
                return !$attribute instanceof EmbeddedEntityListAttribute
                   && !array_key_exists($attribute->getName(), $default_attributes);
            }
        );

        $default_values = [];
        foreach ($non_default_attributes as $attribute_name => $attribute) {
            if (!$attribute->createValueHolder(true)->isNull()) {
                $default_values[$attribute_name] = $attribute->getDefaultValue();
            }
        }

        return array_merge(
            $default_values,
            $create_data,
            [
                'identifier' => $identifier,
                'uuid' => $uuid,
                'language' => $language,
                'version' => $version,
                'workflow_state' => $state_machine->getInitialState()->getName(),
                'workflow_parameters' => []
            ]
        );
    }

    /**
     * Check if the given command conflicts with any events that have occured since it was issued.
     *
     * @param AggregateRootCommandInterface $command
     */
    protected function guardCommandPreConditions(AggregateRootCommandInterface $command)
    {
        if ($this->getHistory()->isEmpty()) {
            throw new HistoryEmptyError(
                sprintf(
                    'Invalid event history.' .
                    ' No event has been previously applied. At least a %s should be applied.',
                    AggregateRootCreatedEvent::CLASS
                ),
                $this->getType()->getPrefix(),
                $command->getAggregateRootIdentifier(),
                $command->getKnownRevision()
            );
        }

        if ($this->getHistory()->getLast()->getSeqNumber() < $command->getKnownRevision()) {
            throw new CommandRevisionError(
                'Invalid command revision for aggregate root ' . $this->getIdentifier() .
                '. The current head revision (seq number ' . $this->getHistory()->getLast()->getSeqNumber() .
                ') must not be smaller than the command\'s known revision (' . $command->getKnownRevision() . ').',
                $this->getType()->getPrefix(),
                $this->getIdentifier(),
                $this->getRevision()
            );
        }

        if ($this->getHistory()->getLast()->getSeqNumber() > $command->getKnownRevision()) {
            $conflicting_events = $this->getHistory()->reverse()->filter(
                function (AggregateRootEventInterface $event) use ($command) {
                    return $event->getSeqNumber() > $command->getKnownRevision()
                        && $command->conflictsWith($event);
                }
            );

            if (!$conflicting_events->isEmpty()) {
                throw new HistoryConflictError(
                    'Command conflicts with known event stream of aggregate root ' . $this->getIdentifier() .
                    ' – command known revision is ' . $command->getKnownRevision() . ' whileas the last known ' .
                    'history sequence number is ' . $this->getHistory()->getLast()->getSeqNumber() . '.',
                    $this->getType()->getPrefix(),
                    $this->getIdentifier(),
                    $this->getRevision()
                );
            }
        }
    }

    protected function guardEventPreConditions(AggregateRootEventInterface $event)
    {
        if (!$event instanceof AggregateRootCreatedEvent
            && $this->getIdentifier() !== $event->getAggregateRootIdentifier()
        ) {
            throw new WrongIdentifierError(
                sprintf(
                    'The AR\'s current identifier (%s) does not match the given event\'s AR identifier (%s).',
                    $this->getIdentifier(),
                    $event->getAggregateRootIdentifier()
                )
            );
        }

        if (!$event instanceof AggregateRootCreatedEvent && !$event instanceof AggregateRootModifiedEvent) {
            throw new UnsupportedEventTypeError(
                sprintf(
                    'Unsupported domain event-type "%s" given. Supported event-types are: %s.',
                    get_class($event),
                    implode(', ', [ AggregateRootCreatedEvent::CLASS, AggregateRootModifiedEvent::CLASS ])
                )
            );
        }

        $last_event = $this->getHistory()->getLast();

        if ($last_event && $event->getSeqNumber() !== $this->getRevision() + 1) {
            throw new InvalidSequenceNumberError(
                sprintf(
                    'Invalid sequence-number. ' .
                    'The given event sequence-number(%d) must be incremental relative to the known-revision(%s).',
                    $event->getSeqNumber(),
                    $this->getRevision()
                )
            );
        }
    }

    /**
     * Process the given command, hence build the corresponding aggregate-root-event.
     *
     * @param AggregateRootTypeCommandInterface $command
     * @param array $custom_event_state
     *
     * @return AggregateRootEventInterface
     */
    protected function processCommand(AggregateRootTypeCommandInterface $command, array $custom_event_state = [])
    {
        $event_class = $command->getEventClass();
        $default_event_state = [
            'metadata' => $command->getMetadata(),
            'uuid' => $command->getUuid(),
            'seq_number' => $this->getRevision() + 1,
            'aggregate_root_type' => $command->getAggregateRootType()
        ];

        if ($command instanceof AggregateRootCommandInterface) {
            $default_event_state['aggregate_root_identifier'] = $command->getAggregateRootIdentifier();
        } elseif (!isset($custom_event_state['aggregate_root_identifier'])) {
            throw new MissingIdentifierError(
                'Missing required "aggregate_root_identifier" attribute for building domain-event.'
            );
        }
        $embedded_entity_events = new EmbeddedEntityEventList();
        foreach ($command->getEmbeddedEntityCommands() as $embedded_command) {
            $embedded_entity_events->push($this->processEmbeddedEntityCommand($embedded_command));
        }
        $default_event_state['embedded_entity_events'] = $embedded_entity_events;

        return new $event_class(array_merge($custom_event_state, $default_event_state));
    }

    /**
     * Process the given aggregate-command, hence build the corresponding aggregate-event.
     *
     * @param CommandInterface $command
     * @param array $custom_event_state
     *
     * @return EmbeddedEntityEventInterface
     */
    protected function processEmbeddedEntityCommand(CommandInterface $command, array $custom_event_state = [])
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
                $create_data['identifier'] = UuidAttribute::generateVersion4();
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
        $embedded_entity_events = new EmbeddedEntityEventList();
        foreach ($command->getEmbeddedEntityCommands() as $embedded_command) {
            $embedded_entity_events->push($this->processEmbeddedEntityCommand($embedded_command));
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
        $this->guardEventPreConditions($event);
        if (!$this->setValues($event->getData())) {
            $errors = [];
            foreach ($this->getValidationResults() as $validation_result) {
                foreach ($validation_result->getViolatedRules() as $violated_rule) {
                    foreach ($violated_rule->getIncidents() as $incident) {
                        $errors[] = PHP_EOL . $validation_result->getSUbject()->getName() .
                            ' - ' . $violated_rule->getName() .
                            ' > ' . $incident->getName() . ': ' . print_r($incident->getParameters(), true);
                    }
                }
            }
            throw new InvalidStateError(
                sprintf(
                    "Aggregate-root is in an invalid state after applying %s.\nErrors:%s",
                    get_class($event),
                    implode(PHP_EOL, $errors)
                )
            );
        }

        $embedded_entity_events = new EmbeddedEntityEventList();
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
            //$notice = 'Applied event %s for %s did not trigger any state changes, so it is being dropped ...';
            //error_log(sprintf($notice, $event, $this));
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
