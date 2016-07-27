<?php

/**
 * Test cases are described by related flow chart images
 * @codeCoverageIgnore
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
            'title' => 'Doom 7'
        ],
        'aggregate_root_identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
        'aggregate_root_type' => 'honeybee-tests.game_schema.game',
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
                    'identifier' => 'f1e876f5-7648-42ea-b813-a6c4bd3c8e3f',
                    'attempts' => 0
                ],
                'position' => 0,
                'embedded_entity_identifier' => 'f1e876f5-7648-42ea-b813-a6c4bd3c8e3f',
                'embedded_entity_type' => 'challenge',
                'parent_attribute_name' => 'challenges',
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
            '@type' => 'honeybee-tests.game_schema.player::projection.standard',
            'identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 1,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:52:37.371793+00:00',
            'modified_at' => '2016-03-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'name' => 'Mock Player',
            'location' => [ 'lon' => 1.2, 'lat' => 2.1 ],
            'profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => '7b446909-9e43-42fb-a043-969463747e2a',
                    'alias' => 'mockprofile1',
                    'tags' => [ 'mock', 'player', 'profile', 'one' ],
                    'teams' => [
                        [
                            '@type' => 'team',
                            'identifier' => '704856e1-28a3-4069-8055-4ff4fd2f3b83',
                            'referenced_identifier' =>
                                'honeybee.fixtures.team-5cf33cf1-554b-40be-98e7-ef7b4e98ec8c-de_DE-1',
                            'name' => 'Super Clan'
                        ]
                    ],
                    'badges' => [
                        [
                            '@type' => 'badge',
                            'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                            'award' => 'High Score'
                        ],
                        [
                            '@type' => 'badge',
                            'identifier' => '06c5fab2-639d-4832-86dc-11d6d6d05ed2',
                            'award' => 'Low Score'
                        ]
                    ]
                ]
            ],
            'simple_profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                    'alias' => 'simpleprofile1',
                    'tags' => [ 'simple', 'player', 'profile', 'one' ],
                    'teams' => [
                        [
                            '@type' => 'team',
                            'identifier' => '179f4ed5-e7a5-4188-a204-5d1bc90d4413',
                            'referenced_identifier' =>
                                'honeybee.fixtures.team-5cf33cf1-554b-40be-98e7-ef7b4e98ec8c-de_DE-1',
                            'name' => 'Super Clan'
                        ]
                    ],
                    'badges' => []
                ]
            ]
        ]
    ],
    'expectations' => [
        [
            '@type' => 'honeybee-tests.game_schema.game::projection.standard',
            'identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 1,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:52:37.371793+00:00',
            'modified_at' => '2016-04-28T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Doom 7',
            'challenges' => [
                [
                    '@type' => 'challenge',
                    'identifier' => 'f1e876f5-7648-42ea-b813-a6c4bd3c8e3f',
                    'attempts' => 0
                ]
            ],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                    'referenced_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'name' => 'Mock Player',
                    'tagline' => '',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '7b446909-9e43-42fb-a043-969463747e2a',
                            'nickname' => '',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'player', 'profile', 'one' ],
                            'badges' => [
                                [
                                    '@type' => 'badge',
                                    'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                                    'award' => 'High Score'
                                ],
                                [
                                    '@type' => 'badge',
                                    'identifier' => '06c5fab2-639d-4832-86dc-11d6d6d05ed2',
                                    'award' => 'Low Score'
                                ]
                            ],
                            'memberships' => [
                                [
                                    '@type' => 'membership',
                                    'identifier' => '704856e1-28a3-4069-8055-4ff4fd2f3b83',
                                    'referenced_identifier' =>
                                        'honeybee.fixtures.team-5cf33cf1-554b-40be-98e7-ef7b4e98ec8c-de_DE-1',
                                    'name' => 'Super Clan'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
