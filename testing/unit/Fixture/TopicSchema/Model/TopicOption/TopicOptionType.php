<?php

namespace Honeybee\Tests\Fixture\TopicSchema\Model\TopicOption;

use Honeybee\Tests\Fixture\TopicSchema\Model\AggregateRootType;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class TopicOptionType extends AggregateRootType
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
