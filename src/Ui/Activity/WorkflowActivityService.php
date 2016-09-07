<?php

namespace Honeybee\Ui\Activity;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Projection\WorkflowSubject;
use Honeybee\ServiceLocatorInterface;
use Honeybee\Ui\Activity\ActivityContainerMap;
use Trellis\Common\Object;

class WorkflowActivityService extends Object
{
    protected $service_locator;

    protected $activity_container_map;

    public function __construct(ServiceLocatorInterface $service_locator, ActivityContainerMap $activity_container_map)
    {
        $this->service_locator = $service_locator;
        $this->activity_container_map = $activity_container_map;
    }

    public function getActivities(AggregateRootTypeInterface $aggregate_root_type)
    {
        $workflow_activities = [];

        $state_machine = $aggregate_root_type->getWorkflowStateMachine();
        foreach ($state_machine->getStates() as $state) {
            if (preg_match('/_task$/', $state->getName())) {
                continue; // final states can't have activities
            }
            $step_activities = new ActivityMap();

            if (!$state->isFinal()) {
                foreach ($state_machine->getTransitions($state->getName()) as $event_name => $transitions) {
                    $step_activities->setItem(
                        $event_name,
                        $this->createWorkflowActivity($aggregate_root_type, $event_name)
                    );
                }
                $read_only_actions = [];
                if ($state->hasOption('read_only_actions')) {
                    $read_only_actions = $state->getOption('read_only_actions')->toArray();
                }
                foreach ($read_only_actions as $action_name => $read_only_action) {
                    $step_activities->setItem(
                        $action_name,
                        $this->createReadOnlyActivity($aggregate_root_type, $action_name, $read_only_action)
                    );
                }
            }

            $workflow_activities[$state->getName()] = $step_activities;
        }

        return $workflow_activities;
    }

    protected function createReadOnlyActivity(
        AggregateRootTypeInterface $aggregate_root_type,
        $action_name,
        array $action_options
    ) {
        return new Activity(
            [
                'name' => $action_name,
                'scope' => $aggregate_root_type->getPrefix(),
                'label' => sprintf('%s.label', $action_name),
                'type' => Activity::TYPE_WORKFLOW,
                'description' => sprintf('%s.description', $action_name),
                'verb' => 'read',
                'rels' => [ $action_name ],
                'settings' => new Settings([ 'form_id' => 'randomId-' . rand() ]),
                'url' => new Url(
                    [
                        'type' => Url::TYPE_ROUTE,
                        'value' => $action_options['route']
                    ]
                )
            ]
        );
    }

    protected function createWorkflowActivity(AggregateRootTypeInterface $aggregate_root_type, $workflow_event)
    {
        $write_events = WorkflowSubject::getWriteEventNames();
        if (in_array($workflow_event, $write_events)) {
            $request_method = 'write';
            $activity_route = $aggregate_root_type->getPrefix() . '.resource.task.proceed';
        } else {
            $request_method = 'read';
            $activity_route = $aggregate_root_type->getPrefix() . '.resource.task';
        }

        return new Activity(
            [
                'name' => $workflow_event,
                'scope' => $aggregate_root_type->getPrefix(),
                'label' => sprintf('%s.label', $workflow_event),
                'type' => Activity::TYPE_WORKFLOW,
                'description' => sprintf('%s.description', $workflow_event),
                'verb' => $request_method,
                'rels' => [ $workflow_event,  sprintf('%s_resource', $workflow_event) ],
                'settings' => new Settings([ 'form_id' => 'randomId-' . rand() ]),
                'url' => new Url(
                    [
                        'type' => Url::TYPE_ROUTE,
                        'value' => $activity_route,
                        'parameters' =>  [ 'event' => $workflow_event ]
                    ]
                )
            ]
        );
    }
}
