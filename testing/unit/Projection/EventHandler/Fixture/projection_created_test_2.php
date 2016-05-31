<?php

/*
 *  Test cases are described by related flow chart images
 */
return [
    'event_state' => [
        '@type' => 'Honeybee\Tests\Fixture\GameSchema\Task\GameCreatedEvent',
        'data' => [
            'identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'workflow_state' => 'edit',
            'title' => 'Dan Dare'
        ],
        'aggregate_root_identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
        'aggregate_root_type' => 'Honeybee\Tests\Fixture\GameSchema\Model\Game\GameType',
        'embedded_entity_events' => [
            [
                '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\EmbeddedEntityAddedEvent',
                'data' => [
                    'identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                    'referenced_identifier' =>
                        'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1'
                ],
                'position' => 0,
                'embedded_entity_identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                'embedded_entity_type' => 'player',
                'parent_attribute_name' => 'players',
                'embedded_entity_events' => []
            ],
            [
                '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\EmbeddedEntityAddedEvent',
                'data' => [
                    'identifier' => 'd058229c-ef88-4897-92a8-df155e863a2f',
                    'referenced_identifier' =>
                        'honeybee.fixtures.player-48fde7c4-7889-42db-8522-6106bf85d58a-de_DE-1'
                ],
                'position' => 1,
                'embedded_entity_identifier' => 'd058229c-ef88-4897-92a8-df155e863a2f',
                'embedded_entity_type' => 'player',
                'parent_attribute_name' => 'players',
                'embedded_entity_events' => []
            ]
        ],
        'seq_number' => 1,
        'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
        'iso_date' => '2016-04-28T10:52:37.371793+00:00',
        'metadata' => []
    ],
    'projections' => [
        [
            '@type' => 'Honeybee\Tests\Fixture\GameSchema\Projection\Player\Player',
            'identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 1,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:52:37.371793+00:00',
            'modified_at' => '2016-03-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'name' => 'Player 1 No Profiles',
            'location' => [ 'lon' => 1.2, 'lat' => 2.1 ],
            'profiles' => [],
            'simple_profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                    'alias' => 'hiddenprofile2',
                    'tags' => [ 'hidden', 'player', 'profile', 'two' ],
                    'badges' => [
                        [
                            '@type' => 'badge',
                            'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                            'award' => 'High Score'
                        ]
                    ],
                    'unmirrored_badges' => [],
                    'teams' => []
                ]
            ]
        ],
        [
            '@type' => 'Honeybee\Tests\Fixture\GameSchema\Projection\Player\Player',
            'identifier' => 'honeybee.fixtures.player-48fde7c4-7889-42db-8522-6106bf85d58a-de_DE-1',
            'revision' => 1,
            'uuid' => '48fde7c4-7889-42db-8522-6106bf85d58a',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-26T10:52:37.371793+00:00',
            'modified_at' => '2016-03-26T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'name' => 'Player 2 No Profiles',
            'location' => [ 'lon' => 2.313, 'lat' => 2.09 ],
            'profiles' => [],
            'simple_profiles' => []
        ]
    ],
    'expectations' => [
        [
            '@type' => 'Honeybee\Tests\Fixture\GameSchema\Projection\Game\Game',
            'identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 1,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:52:37.371793+00:00',
            'modified_at' => '2016-04-28T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Dan Dare',
            'challenges' => [],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                    'referenced_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Player 1 No Profiles',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => []
                ],
                [
                    '@type' => 'player',
                    'identifier' => 'd058229c-ef88-4897-92a8-df155e863a2f',
                    'referenced_identifier' => 'honeybee.fixtures.player-48fde7c4-7889-42db-8522-6106bf85d58a-de_DE-1',
                    'tagline' => '',
                    'name' => 'Player 2 No Profiles',
                    'area' => [ 'lon' => 2.313, 'lat' => 2.09 ],
                    'profiles' => []
                ]
            ]
        ]
    ]
];
