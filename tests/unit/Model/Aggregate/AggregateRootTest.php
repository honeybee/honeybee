<?php

namespace Honeybee\Tests\Model\Aggregate;

use Honeybee\Common\Error\AggregateRoot\CommandRevisionError;
use Honeybee\Common\Error\AggregateRoot\HistoryConflictError;
use Honeybee\Common\Error\AggregateRoot\HistoryEmptyError;
use Honeybee\Infrastructure\Workflow\XmlStateMachineBuilder;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\ServiceLocatorInterface;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\AuthorCreatedEvent;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\ModifyAuthor\AuthorModifiedEvent;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\ModifyAuthor\ModifyAuthorCommand;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\ProceedAuthorWorkflow\AuthorWorkflowProceededEvent;
use Honeybee\Tests\Fixture\BookSchema\Model\Task\ProceedAuthorWorkflow\ProceedAuthorWorkflowCommand;
use Honeybee\Tests\TestCase;
use Workflux\Guard\GuardInterface;

class AggregateRootTest extends TestCase
{
    const AGGREGATE_ROOT_IDENTIFIER =
        'honeybee_cmf.aggregate_fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';

    const AGGREGATE_ROOT_PREFIX = 'author';

    const AGGREGATE_ROOT_TYPE = 'honeybee_cmf.aggregate_fixtures.author';

    const AGGREGATE_ROOT_UUID = 'fa44c523-592f-404f-bcd5-00f04ff5ce61';

    const AGGREGATE_ROOT_LANGUAGE = 'de_DE';

    protected $aggregate_root_type;

    public function setUp()
    {
        $this->aggregate_root_type = new AuthorType();
    }

    /**
     * Expects a correct creation of the aggregate-root, with initialization
     * of its mandatory attributes and of its event-history.
     *
     * Note: The list of mandatory attributes can be read into the aggregate-root
     * entity XML definition or into the Type class for the specific aggregate-root.
     */
    public function testCreate()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $this->assertTrue($aggregate_root->isValid());

        $create_command = new CreateAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'values' => [
                    'firstname' => 'Mark',
                    'lastname' => 'Twain'
                ]
            ]
        );

        $aggregate_root->create($create_command, $this->getDefaultStateMachine());

        $this->assertTrue($aggregate_root->isValid());
        $this->assertEquals('Mark', $aggregate_root->getFirstname());
        $this->assertEquals('Twain', $aggregate_root->getLastname());
        $this->assertCount(1, $aggregate_root->getUncomittedEvents());
        $this->assertCount(1, $aggregate_root->getHistory());
    }

    /**
     * @expectedException \TypeError
     */
    public function testCreateWithNonCreateCommand()
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('PHP7+ only test');
            return;
        }

        $aggregate_root = $this->constructAggregateRoot();
        $this->assertTrue($aggregate_root->isValid());

        $create_command = new ModifyAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'values' => [
                    'firstname' => 'Mark',
                    'lastname' => 'Twain'
                ]
            ]
        );

        // @codeCoverageIgnoreStart
        $aggregate_root->create($create_command);
    } // @codeCoverageIgnoreEnd

    /**
     * The create command requires an aggregate root type to be specified
     *
     * @expectedException Assert\InvalidArgumentException
     */
    public function testCreateWithoutType()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $this->assertTrue($aggregate_root->isValid());

        $create_command = new CreateAuthorCommand(
            [
                'values' => [
                    'firstname' => 'Mark',
                    'lastname' => 'Twain'
                ]
            ]
        );

        // @codeCoverageIgnoreStart
        $aggregate_root->create($create_command);
    } // @codeCoverageIgnoreEnd

    /**
     * Expects a non-valid aggregate-root when trying to create it with
     * missing values for mandatory attributes.
     */
    public function testCreateWithoutMandatoryAttribute()
    {
        $this->markTestIncomplete();
        $aggregate_root = $this->constructAggregateRoot();
        $this->assertTrue($aggregate_root->isValid());

        $create_command = new CreateAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'values' => [
                    'firstname' => 'Mark'
                    // Value for the mandatory attribute 'lastname' is missing
                ]
            ]
        );

        $aggregate_root->create($create_command);

        $this->assertFalse(
            $aggregate_root->isValid(),
            'The AggregateRoot should not be valid when mandatory attributes are not set.'
        );
        $this->assertEquals('Mark', $aggregate_root->getFirstname());
    }

    /**
     * Expects a correct modification of the aggregate-root attributes, according
     * to the 'values' payload passed into the Command.
     */
    public function testModify()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $modify_command = new ModifyAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'values' => [ 'lastname' => 'Wahlberg' ]
            ]
        );
        $aggregate_root->modify($modify_command);

        $this->assertTrue($aggregate_root->isValid());
        $this->assertEquals('Wahlberg', $aggregate_root->getLastname());
        $this->assertCount(1, $aggregate_root->getUncomittedEvents());
        $this->assertCount(2, $aggregate_root->getHistory());
    }

    /**
     * The modify command requires an aggregate root type to be specified
     *
     * @expectedException Assert\InvalidArgumentException
     */
    public function testModifyWithoutType()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $modify_command = new ModifyAuthorCommand(
            [
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'values' => [ 'lastname' => 'Wahlberg' ]
            ]
        );

        // @codeCoverageIgnoreStart
        $aggregate_root->create($modify_command);
    } // @codeCoverageEnd

    /**
     * Expects an exception when providing an aggregate-root-identifier that does
     * not correspond to the aggregate-root which the command is processed upon.
     *
     * @expectedException Assert\InvalidArgumentException
     */
    public function testModifyInvalidAggregateRootIdentifier()
    {
        $modify_command = new ModifyAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => 'invalid aggregate root identifier',
                'known_revision' => 1,
                'values' => [ 'lastname' => 'Wahlberg' ]
            ]
        );
    } // @codeCoverageIgnore

    /**
     * Expects an exception when trying to process a command without
     * having a CreatedEvent in the history.
     */
    public function testModifyWithoutCreatedEvent()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $modify_command = new ModifyAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'values' => [ 'lastname' => 'Wahlberg' ]
            ]
        );

        try {
            $aggregate_root->modify($modify_command);
            $this->fail('Exception expected: '.HistoryEmptyError::class);
        } catch (HistoryEmptyError $e) {
            $this->assertSame(self::AGGREGATE_ROOT_TYPE, $e->getType());
            $this->assertSame(self::AGGREGATE_ROOT_IDENTIFIER, $e->getIdentifier());
            $this->assertSame(1, $e->getRevision());
        }
    } // @codeCoverageIgnore

    /**
     * Expects an exception when trying to process a command that
     * conflicts with the domainevent history fo the AR.
     */
    public function testModifyConflictsWithHistoryOfEvents()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $aggregate_root->reconstituteFrom($this->getHistoryFixture());

        $modify_command = new class(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 3,
                'values' => [ 'lastname' => 'Wahlberg' ]
            ]
        ) extends ModifyAuthorCommand {
            public function conflictsWith(AggregateRootEventInterface $event, array &$conflicting_changes = [])
            {
                $conflicting_changes['someattribute'] = 'somevalue';
                return true;
            }
        };

        try {
            $aggregate_root->modify($modify_command);
            $this->fail('Exception expected: '.HistoryConflictError::class);
        } catch (HistoryConflictError $e) {
            $this->assertSame(self::AGGREGATE_ROOT_TYPE, $e->getType());
            $this->assertSame(self::AGGREGATE_ROOT_IDENTIFIER, $e->getIdentifier());
            $this->assertSame(4, $e->getRevision());
        }
    } // @codeCoverageIgnore

    /**
     * Expects a valid number of items in the event-history and in the list of
     * not-committed events, after the call to the 'markAsComitted' method.
     */
    public function testMarkAsComitted()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $aggregate_root->reconstituteFrom($this->getHistoryFixture());

        $new_command = new ModifyAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 4,
                'values' => [ 'firstname' => 'Samantha' ]
            ]
        );

        $aggregate_root->modify($new_command);
        $this->assertCount(1, $aggregate_root->getUncomittedEvents());

        $aggregate_root->markAsComitted();

        $this->assertCount(0, $aggregate_root->getUncomittedEvents());
        $this->assertCount(5, $aggregate_root->getHistory());
    }

    /**
     * Expects an aggregate-root to be correctly reconstituted to a specific
     * state determined by a list of past events.
     */
    public function testReconstituteFromEventList()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = $this->getHistoryFixture();
        $aggregate_root->reconstituteFrom($events_history);

        $this->assertTrue($aggregate_root->isValid());
        $this->assertCount(0, $aggregate_root->getUncomittedEvents());
        $this->assertCount(4, $aggregate_root->getHistory());
        $this->assertEquals('Donnie', $aggregate_root->getFirstname());
        $this->assertEquals('Darko', $aggregate_root->getLastname());
    }

    /**
     * Expects a second reconstitution from a different event list to
     * generate a different aggregate-root, with no data related to the
     * first aggregate-root.
     *
     * @expectedException Honeybee\Common\Error\AggregateRoot\ReconstitutionError
     */
    public function testReconstituteFromEventListTwice()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = $this->getHistoryFixture();
        $alternate_events_history = $this->getAlternativeHistoryFixture();

        $aggregate_root->reconstituteFrom($events_history);

        $aggregate_root->reconstituteFrom($alternate_events_history);
    } // @codeCoverageIgnore

    /**
     * Expects an exception when trying to force a non valid sequence-number
     * into a manually created event.
     *
     * @expectedException Honeybee\Common\Error\AggregateRoot\InvalidSequenceNumberError
     */
    public function testReconstituteFromInvalidEventSequenceNumber()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = $this->getHistoryFixture();

        $events_history->push(
            new AuthorModifiedEvent([
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'uuid' => '9d3cefd9-f5f1-4a3f-ad2b-231a6d50eba7',
                'seq_number' => 100,
                'data' => [ 'lastname' => 'nice-try-wont-work' ]
            ])
        );

        $aggregate_root->reconstituteFrom($events_history);
    } // @codeCoverageIgnore

    /**
     * Expects an exception when trying to force a non valid known-revision
     * into a command.
     */
    public function testReconstituteFromInvalidCommandKnownRevision()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = $this->getHistoryFixture();
        $aggregate_root->reconstituteFrom($events_history);

        $wrong_seq_number_command = new ModifyAuthorCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 100,
                'values' => [ 'firstname' => 'Samantha' ]
            ]
        );

        try {
            $aggregate_root->modify($wrong_seq_number_command);
            $this->fail('Exception expected: '.CommandRevisionError::class);
        } catch (CommandRevisionError $e) {
            $this->assertSame(self::AGGREGATE_ROOT_TYPE, $e->getType());
            $this->assertSame(self::AGGREGATE_ROOT_IDENTIFIER, $e->getIdentifier());
            $this->assertSame(4, $e->getRevision());
        }
    } // @codeCoverageIgnore

    /**
     * Expects an exception when trying to reconstitute an aggregate-root
     * from an event list where the first event is not a CreatedEvent.
     *
     * @expectedException Honeybee\Common\Error\AggregateRoot\ReconstitutionError
     */
    public function testReconstituteFromEventListWithNoCreatedEvent()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = new AggregateRootEventList(
            [
                new AuthorModifiedEvent(
                    [
                        'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                        'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                        'uuid' => '9d3cefd9-f5f1-4a3f-ad2b-8d146d50eba7',
                        'seq_number' => 1,
                        'data' => [
                            'lastname' => 'Wahlberg'
                        ]
                    ]
                )
            ]
        );
        $aggregate_root->reconstituteFrom($events_history);
    } // @codeCoverageIgnore

    /**
     * Expects a succesful workflow state transition, according to the
     * definition of the aggregate-root's state-machine.
     */
    public function testProceedWorkflow()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $workflow_command = new ProceedAuthorWorkflowCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'current_state_name' => 'inactive',
                'event_name' => 'promote'
            ]
        );

        $aggregate_root->proceedWorkflow($workflow_command, $this->getDefaultStateMachine());

        $this->assertTrue($aggregate_root->isValid());
        $this->assertEquals('active', $aggregate_root->getWorkflowState());
    }

    /**
     * Expects an exception with a specific message, when trying to proceed
     * to a workflow state without having first created the aggregate-root.
     *
     * @expectedException Honeybee\Common\Error\AggregateRoot\HistoryEmptyError
     * @expectedExceptionMessage Invalid event history. No event has been previously applied.
     */
    public function testProceedWorkflowWithoutCreation()
    {
        $aggregate_root = $this->constructAggregateRoot();

        $workflow_command = new ProceedAuthorWorkflowCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 0,
                'current_state_name' => 'inactive',
                'event_name' => 'promote'
            ]
        );

        $aggregate_root->proceedWorkflow($workflow_command, $this->getDefaultStateMachine());
    } // @codeCoverageIgnore

    /**
     * Expects an exception when trying to proceed in the workflow providing
     * a not valid current state.
     *
     * @expectedException Honeybee\Common\Error\AggregateRoot\WorkflowStateMismatchError
     */
    public function testProceedWorkflowInvalidCurrentState()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $workflow_command = new ProceedAuthorWorkflowCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'current_state_name' => 'active',
                'event_name' => 'promote'
            ]
        );

        $aggregate_root->proceedWorkflow($workflow_command, $this->getDefaultStateMachine());
    } // @codeCoverageIgnore

    /**
     * Expects an exception when trying to proceed in the workflow state
     * but providing an invalid transition event (according to the
     * state-machine definition).
     *
     * @expectedException Workflux\Error\Error
     */
    public function testProceedWorkflowInvalidEvent()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $workflow_command = new ProceedAuthorWorkflowCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'current_state_name' => 'inactive',
                'event_name' => 'demote'
            ]
        );

        $aggregate_root->proceedWorkflow($workflow_command, $this->getDefaultStateMachine());
    } // @codeCoverageIgnore

    /**
     * Expects a correctly initialised UUID attribute.
     */
    public function testGetUuid()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $this->assertEquals(self::AGGREGATE_ROOT_UUID, $aggregate_root->getUuid());
    }

    /**
     * Expects a correctly initialised Language attribute.
     */
    public function testGetLanguage()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $this->assertEquals(self::AGGREGATE_ROOT_LANGUAGE, $aggregate_root->getLanguage());
    }

    /**
     * Expects a correctly initialised Version attribute.
     */
    public function testGetVersion()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = $this->getHistoryFixture();
        $aggregate_root->reconstituteFrom($events_history);

        $this->assertEquals(1, $aggregate_root->getVersion());
    }

    /**
     * Expects a correctly initialised Revision attribute.
     */
    public function testGetRevision()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = $this->getHistoryFixture();
        $aggregate_root->reconstituteFrom($events_history);

        $this->assertEquals(4, $aggregate_root->getRevision());
    }

    /**
     * Expects the workflow-state to be inactive, as specified into the state-machine
     * definition, when no workflow event has been applied before.
     */
    public function testGetWorkflowState()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $this->assertEquals('inactive', $aggregate_root->getWorkflowState());
    }

    /**
     * Expects the workflow-parameters (specified into the state-machine definition)
     * to be readable after the workflow state transition.
     */
    public function testGetWorkflowParameters()
    {
        $this->markTestIncomplete();
        $aggregate_root = $this->getCreatedAggregateRoot();

        $expected_workflow_parameters = [
            'task_action' => [
                'module' => 'Author',
                'action' => 'Resource.Modify'
            ]
        ];

        $workflow_command = new ProceedAuthorWorkflowCommand(
            [
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'known_revision' => 1,
                'current_state_name' => 'inactive',
                'event_name' => 'edit',
                'values' => [
                    'workflow_parameters' => $expected_workflow_parameters
                ]
            ]
        );

        $aggregate_root->proceedWorkflow($workflow_command, $this->getDefaultStateMachine());

        $this->assertTrue($aggregate_root->isValid());
        $this->assertEquals($expected_workflow_parameters, $aggregate_root->getWorkflowParameters());
    }

    /**
     * Expects a string representation of the aggregate-root.
     */
    public function testToString()
    {
        $aggregate_root = $this->getCreatedAggregateRoot();

        $this->assertInternalType('string', $aggregate_root->__toString());
        $this->assertEquals(self::AGGREGATE_ROOT_IDENTIFIER, $aggregate_root->__toString());
    }

    // ------------------------------ helpers ------------------------------

    protected function getDefaultStateMachine()
    {
        $service_locator_stub = $this->createMock(ServiceLocatorInterface::CLASS);
        $service_locator_stub->method('make')->will(
            $this->returnCallback(function ($impl, $more) {
                switch ($impl) {
                    case 'Workflux\State\VariableState':
                    case 'Workflux\State\State':
                        return new $impl($more[':name'], $more[':type'], $more[':options']);
                        break;
                    case 'Workflux\Guard\VariableGuard':
                        return $this->createMock(GuardInterface::CLASS);
                        break;
                    default:
                        return null;
                }
            })
        );

        $workflows_file_path = __DIR__ . '/../../Fixture/BookSchema/Model/workflows.xml';

        $workflow_builder = new XmlStateMachineBuilder(
            [
                'name' => self::AGGREGATE_ROOT_TYPE . '.default_workflow',
                'state_machine_definition' => $workflows_file_path
            ],
            $service_locator_stub
        );

        return $workflow_builder->build();
    }

    protected function constructAggregateRoot()
    {
        return $this->aggregate_root_type->createEntity();
    }

    protected function getCreatedAggregateRoot()
    {
        $aggregate_root = $this->constructAggregateRoot();
        $events_history = $this->getAuthorCreatedEventHistory();
        $aggregate_root->reconstituteFrom($events_history);

        return $aggregate_root;
    }

    protected function getHistoryFixture()
    {
        $history_fixture = new AggregateRootEventList;
        $history_fixture->push(
            new AuthorCreatedEvent([
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'uuid' => '26cfb993-5946-4bd3-befe-8fb92648fd27',
                'seq_number' => 1,
                'data' => [
                    'firstname' => 'Mark',
                    'lastname' => 'Twain',
                    'blurb' => 'the grinch',
                    // Command generated attributes
                    'identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                    'uuid' => self::AGGREGATE_ROOT_UUID,
                    'language' => self::AGGREGATE_ROOT_LANGUAGE,
                    'version' => 1,
                    'workflow_state' => 'inactive',
                    'workflow_parameters' => []
                ]
            ])
        );
        $history_fixture->push(
            new AuthorModifiedEvent([
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'uuid' => '9d3cefd9-f5f1-4a3f-ad2b-8d146d50eba7',
                'seq_number' => 2,
                'data' => [
                    'lastname' => 'Wahlberg'
                ]
            ])
        );
        $history_fixture->push(
            new AuthorModifiedEvent([
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'uuid' => '39e3d80a-d700-4c1f-8bc7-0c3141b94af7',
                'seq_number' => 3,
                'data' => [
                    'firstname' => 'Donnie'
                ]
            ])
        );
        $history_fixture->push(
            new AuthorModifiedEvent([
                'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'uuid' => '6d3f60a0-3662-47ad-a7f0-1eaf33bb46b0',
                'seq_number' => 4,
                'data' => [
                    'lastname' => 'Darko'
                ]
            ])
        );

        return $history_fixture;
    }

    protected function getAlternativeHistoryFixture()
    {
        $first_event = new AuthorCreatedEvent([
            'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
            'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
            'uuid' => '968bbade-5182-411f-9d02-39376035a068',
            'seq_number' => 1,
            'data' => [
                'firstname' => 'Stanley',
                // Command generated attributes
                'identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                'uuid' => self::AGGREGATE_ROOT_UUID,
                'language' => self::AGGREGATE_ROOT_LANGUAGE,
                'version' => 1,
                'workflow_state' => 'inactive',
                'workflow_parameters' => []
            ]
        ]);

        $second_event = new AuthorWorkflowProceededEvent([
            'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
            'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
            'uuid' => '81b07c06-33f6-4c2e-8675-ad9b493d8142',
            'seq_number' => 2,
            'data' => [
                'workflow_state' => 'active'
            ]
        ]);

        return new AggregateRootEventList([ $first_event, $second_event ]);
    }

    protected function getAuthorCreatedEventHistory()
    {
        return new AggregateRootEventList(
            [
                new AuthorCreatedEvent(
                    [
                        'aggregate_root_type' => self::AGGREGATE_ROOT_TYPE,
                        'aggregate_root_identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                        'uuid' => '26cfb993-5946-4bd3-befe-8fb92648fd27',
                        'seq_number' => 1,
                        'data' => [
                            'firstname' => 'Mark',
                            'lastname' => 'Twain',
                            'blurb' => 'the grinch',
                            // Command generated attributes
                            'identifier' => self::AGGREGATE_ROOT_IDENTIFIER,
                            'uuid' => self::AGGREGATE_ROOT_UUID,
                            'language' => self::AGGREGATE_ROOT_LANGUAGE,
                            'version' => 1,
                            'workflow_state' => 'inactive',
                            'workflow_parameters' => []
                        ]
                    ]
                )
            ]
        );
    }
}
