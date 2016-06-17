<?php

return [
    'event' => [
        '@type' => 'Honeybee\Projection\Event\ProjectionCreatedEvent',
        'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
        'projection_type' => 'honeybee-tests.game_schema.player',
        'projection_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
        'data' => [
            '@type' => 'honeybee-tests.game_schema.player',
            'identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 1,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
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
    ]
];
