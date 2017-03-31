<?php

return [
    'event' => [
        '@type' => 'Honeybee\Projection\Event\ProjectionUpdatedEvent',
        'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
        'projection_type' => 'honeybee_tests.game_schema.player::projection.standard',
        'projection_identifier' => 'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
        'data' => [
            '@type' => 'honeybee_tests.game_schema.player::projection.standard',
            'identifier' => 'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 5,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-05-27T10:52:37.371793+00:00',
            'modified_at' => '2016-05-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'name' => 'Garry Kasparov',
            'location' => [ 'lon' => 1.23, 'lat' => 3.21 ],
            'profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                    'alias' => 'mockprofile1',
                    'tags' => [ 'mock', 'profile', 'one' ],
                    'teams' => [
                        [
                            '@type' => 'team',
                            'identifier' => 'f0420515-46fc-410a-929b-3abcfce6995f',
                            'referenced_identifier' =>
                                'honeybee_tests.game_schema.team-2b4dcd5f-672b-4135-8668-dd8efd0abfe3-de_DE-1',
                            'name' => 'Power Shower'
                        ],
                        [
                            '@type' => 'clan',
                            'identifier' => 'd8668418-719e-4c09-886c-c49f45d3ee97',
                            'referenced_identifier' =>
                                'honeybee_tests.game_schema.team-8355decf-6a6f-475f-abce-8cc38ee4ccf9-de_DE-1',
                            'name' => 'Wu-Tang Clan'
                        ]
                    ],
                    'badges' => [
                        [
                            '@type' => 'badge',
                            'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                            'award' => 'High Score'
                        ]
                    ]
                ]
            ],
            'simple_profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                    'alias' => 'hiddenprofile2',
                    'tags' => [ 'hidden', 'profile', 'two' ],
                    'teams' => [],
                    'badges' => []
                ]
            ]
        ],
        'iso_date' => '2016-05-28T10:52:37.371793+00:00',
        'metadata' => []
    ],
    'query' => [
        '@type' => 'Honeybee\Infrastructure\DataAccess\Query\CriteriaQuery',
        'search_criteria_list' => [],
        'filter_criteria_list' => [
            [
                '@type' => 'Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria',
                'attribute_path' => 'identifier',
                'comparison' => [
                    '@type' => 'Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals',
                    'comparator' => 'eq',
                    'comparand' => 'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'inverted' => true
                ]
            ],
            [
                [
                    '@type' => 'Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria',
                    'attribute_path' => 'players.referenced_identifier',
                    'comparison' => [
                        '@type' => 'Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals',
                        'comparator' => 'eq',
                        'comparand' => 'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                        'inverted' => false
                    ]
                ]
            ]
        ],
        'sort_criteria_list' => [],
        'offset' => 0,
        'limit' => 100
    ],
    'projections' => [
        [
            '@type' => 'honeybee_tests.game_schema.game::projection.standard',
            'identifier' => 'honeybee_tests.game_schema.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 3,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:52:35.349643+00:00',
            'modified_at' => '2016-04-28T10:52:35.349643+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Doom 4',
            'challenges' => [
                [
                    '@type' => 'challenge',
                    'identifier' => '5f337a59-44bd-4ad4-9b53-7512a231f0b3',
                    'attempts' => 5
                ]
            ],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => '2b4dcd5f-672b-4135-8668-dd8efd0abfe3',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-c9a1fd68-e6e5-462c-a544-c86f0812cf6c-de_DE-1',
                    'tagline' => '',
                    'name' => 'Mr Bean',
                    'area' => [ 'lon' => 2.313, 'lat' => 2.09 ],
                    'profiles' => []
                ],
                [
                    '@type' => 'player',
                    'identifier' => '95c5ff31-8eca-41d5-95a0-0eb4ac35904b',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Anatoly Karpov',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'nickname' => 'Diaz',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'badges' => [
                                [
                                    '@type' => 'badge',
                                    'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                                    'award' => 'High Score'
                                ]
                            ],
                            'memberships' => []
                        ]
                    ]
                ]
            ]
        ],
        [
            '@type' => 'honeybee_tests.game_schema.game::projection.standard',
            'identifier' => 'honeybee_tests.game_schema.game-5382bd85-3a94-40d1-8745-96ce33e03919-de_DE-1',
            'revision' => 5,
            'uuid' => '5382bd85-3a94-40d1-8745-96ce33e03919',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-29T10:52:35.349643+00:00',
            'modified_at' => '2016-04-29T10:52:35.349643+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Touch Butt',
            'challenges' => [],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => '0d679b3d-0541-463d-951d-96bd6d5fc0b4',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Anatoly Karpov',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'nickname' => 'McGregor',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'badges' => [
                                [
                                    '@type' => 'badge',
                                    'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                                    'award' => 'High Score'
                                ]
                            ],
                            'memberships' => []
                        ]
                    ]
                ],
                [
                    '@type' => 'player',
                    'identifier' => 'acf7818c-3ac5-4202-8f62-f7313fafb1fe',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Anatoly Karpov',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'nickname' => 'Mayweather',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'badges' => [
                                [
                                    '@type' => 'badge',
                                    'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                                    'award' => 'High Score'
                                ]
                            ],
                            'memberships' => []
                        ]
                    ]
                ]
            ]
        ]
    ],
    'expectations' => [
        [
            '@type' => 'honeybee_tests.game_schema.game::projection.standard',
            'identifier' => 'honeybee_tests.game_schema.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 3,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:52:35.349643+00:00',
            'modified_at' => '2016-04-28T10:52:35.349643+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Doom 4',
            'challenges' => [
                [
                    '@type' => 'challenge',
                    'identifier' => '5f337a59-44bd-4ad4-9b53-7512a231f0b3',
                    'attempts' => 5
                ]
            ],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => '2b4dcd5f-672b-4135-8668-dd8efd0abfe3',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-c9a1fd68-e6e5-462c-a544-c86f0812cf6c-de_DE-1',
                    'tagline' => '',
                    'name' => 'Mr Bean',
                    'area' => [ 'lon' => 2.313, 'lat' => 2.09 ],
                    'profiles' => []
                ],
                [
                    '@type' => 'player',
                    'identifier' => '95c5ff31-8eca-41d5-95a0-0eb4ac35904b',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Garry Kasparov',
                    'area' => [ 'lon' => 1.23, 'lat' => 3.21 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'nickname' => 'Diaz',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'badges' => [
                                [
                                    '@type' => 'badge',
                                    'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                                    'award' => 'High Score'
                                ]
                            ],
                            'memberships' => [
                                [
                                    '@type' => 'membership',
                                    'identifier' => 'f0420515-46fc-410a-929b-3abcfce6995f',
                                    'referenced_identifier' =>
                                        'honeybee_tests.game_schema.team-2b4dcd5f-672b-4135-8668-dd8efd0abfe3-de_DE-1',
                                    'name' => 'Power Shower'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            '@type' => 'honeybee_tests.game_schema.game::projection.standard',
            'identifier' => 'honeybee_tests.game_schema.game-5382bd85-3a94-40d1-8745-96ce33e03919-de_DE-1',
            'revision' => 5,
            'uuid' => '5382bd85-3a94-40d1-8745-96ce33e03919',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-29T10:52:35.349643+00:00',
            'modified_at' => '2016-04-29T10:52:35.349643+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Touch Butt',
            'challenges' => [],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => '0d679b3d-0541-463d-951d-96bd6d5fc0b4',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Garry Kasparov',
                    'area' => [ 'lon' => 1.23, 'lat' => 3.21 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'nickname' => 'McGregor',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'badges' => [
                                [
                                    '@type' => 'badge',
                                    'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                                    'award' => 'High Score'
                                ]
                            ],
                            'memberships' => [
                                [
                                    '@type' => 'membership',
                                    'identifier' => 'f0420515-46fc-410a-929b-3abcfce6995f',
                                    'referenced_identifier' =>
                                        'honeybee_tests.game_schema.team-2b4dcd5f-672b-4135-8668-dd8efd0abfe3-de_DE-1',
                                    'name' => 'Power Shower'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '@type' => 'player',
                    'identifier' => 'acf7818c-3ac5-4202-8f62-f7313fafb1fe',
                    'referenced_identifier' =>
                        'honeybee_tests.game_schema.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Garry Kasparov',
                    'area' => [ 'lon' => 1.23, 'lat' => 3.21 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'nickname' => 'Mayweather',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'badges' => [
                                [
                                    '@type' => 'badge',
                                    'identifier' => '3c642c81-dc8b-485c-9b63-3eaade13c7de',
                                    'award' => 'High Score'
                                ]
                            ],
                            'memberships' => [
                                [
                                    '@type' => 'membership',
                                    'identifier' => 'f0420515-46fc-410a-929b-3abcfce6995f',
                                    'referenced_identifier' =>
                                        'honeybee_tests.game_schema.team-2b4dcd5f-672b-4135-8668-dd8efd0abfe3-de_DE-1',
                                    'name' => 'Power Shower'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
