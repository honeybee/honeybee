<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Team;

use Honeybee\Tests\Fixture\GameSchema\Model\AggregateRootType;
use Workflux\StateMachine\StateMachineInterface;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class TeamType extends AggregateRootType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Team',
            [
                new Text('name', $this, [ 'mandatory' => true ])
            ],
            new Options([ 'is_hierarchical' => true ])
        );
    }

    public function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
