<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Team;

use Honeybee\Tests\Fixture\GameSchema\Projection\ProjectionType;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class TeamType extends ProjectionType
{
    public function __construct()
    {
        parent::__construct(
            'Team',
            [
                new Text('name', $this, [ 'mandatory' => true ])
            ],
            new Options([ 'is_hierarchical' => true ])
        );
    }

    public static function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
