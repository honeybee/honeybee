<?php

namespace Honeybee\Tests\Fixtures\GameSchema\Model\Team;

use Honeybee\Tests\Fixtures\GameSchema\Model\EntityType;
use Workflux\StateMachine\StateMachineInterface;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class TeamType extends EntityType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        parent::__construct('Team', $state_machine, new Options([ 'is_hierarchical' => true ]));
    }

    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
            [
                new Text('name', $this, [ 'mandatory' => true ])
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
