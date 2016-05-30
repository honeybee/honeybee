<?php

return [
    'event' => [
        '@type' => 'Honeybee\Projection\ProjectionUpdatedEvent',
        'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
        'projection_type' => 'Honeybee\Tests\Fixtures\GameSchema\Projection\Player\PlayerType',
        'projection_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
        'data' => [
            '@type' => 'Honeybee\Tests\Fixtures\GameSchema\Projection\Player\Player',
            'identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 5,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-05-27T10:52:37.371793+00:00',
            'modified_at' => '2016-05-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'name' => 'Anatoly Karpov',
            'location' => [ 'lon' => 1.2, 'lat' => 2.1 ],
            'profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                    'alias' => 'mockprofile1',
                    'tags' => [ 'mock', 'profile', 'one' ],
                    'teams' => [],
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
                    'alias' => 'this thing changed',
                    'tags' => [ 'changed', 'profile', 'one' ],
                    'teams' => [],
                    'badges' => []
                ]
            ]
        ],
        'iso_date' => '2016-05-28T10:52:37.371793+00:00',
        'metadata' => []
    ],
    'query' => [
        '@type' => 'Honeybee\Infrastructure\DataAccess\Query\Query',
        'search_criteria_list' => [],
        'filter_criteria_list' => [
            [
                '@type' => 'Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria',
                'attribute_path' => 'identifier',
                'comparison' => [
                    '@type' => 'Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals',
                    'comparator' => 'eq',
                    'comparand' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
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
                        'comparand' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                        'inverted' => false
                    ]
                ]
            ]
        ],
        'sort_criteria_list' => [],
        'offset' => 0,
        'limit' => 10000
    ],
    'projections' => [
        [
            '@type' => 'Honeybee\Tests\Fixtures\GameSchema\Projection\Game\Game',
            'identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 3,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'short_id' => 0,
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
                    'identifier' => '95c5ff31-8eca-41d5-95a0-0eb4ac35904b',
                    'referenced_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'name' => 'Anatoly Karpov',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'nickname' => 'Barefoot Gen',
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
            '@type' => 'Honeybee\Tests\Fixtures\GameSchema\Projection\Game\Game',
            'identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 3,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'short_id' => 0,
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
                    'identifier' => '95c5ff31-8eca-41d5-95a0-0eb4ac35904b',
                    'referenced_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'name' => 'Anatoly Karpov',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'profile', 'one' ],
                            'nickname' => 'Barefoot Gen',
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
    ]
];
