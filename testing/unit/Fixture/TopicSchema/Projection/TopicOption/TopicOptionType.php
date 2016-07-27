<?php

namespace Honeybee\Tests\Fixture\TopicSchema\Projection\TopicOption;

use Honeybee\Tests\Fixture\TopicSchema\Projection\ProjectionType;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class TopicOptionType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'TopicOption',
            [
                new TextAttribute('title', $this)
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return TopicOption::CLASS;
    }
}
