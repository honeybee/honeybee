<?php

/*
 *  Aggregate root node moved test
 */
return [
    'event_state' => [
        '@type' => 'Honeybee\Tests\Fixture\GameSchema\Task\TeamNodeMovedEvent',
        'data' => [
            'parent_node_id' => ''
        ],
        'aggregate_root_identifier' => 'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
        'aggregate_root_type' => 'honeybee-tests.game_schema.team',
        'embedded_entity_events' => [],
        'seq_number' => 3,
        'uuid' => 'a44955b9-b548-4a16-8cf3-c3eb33b08eed',
        'iso_date' => '2016-04-28T10:54:37.371793+00:00',
        'metadata' => []
    ],
    'subject' => [
        '@type' => 'honeybee-tests.game_schema.team',
        'identifier' => 'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
        'revision' => 2,
        'uuid' => 'd8668418-719e-4c09-886c-c49f45d3ee97',
        'short_id' => 0,
        'language' => 'de_DE',
        'version' => 1,
        'created_at' => '2016-03-26T10:52:37.371793+00:00',
        'modified_at' => '2016-03-26T10:52:37.371793+00:00',
        'workflow_state' => 'edit',
        'workflow_parameters' => [],
        'metadata' => [],
        'parent_node_id' => 'honeybee.fixtures.team-2d10d19a-7aca-4d87-aa34-1ea9a5604138-de_DE-1',
        'materialized_path' => 'honeybee.fixtures.team-2d10d19a-7aca-4d87-aa34-1ea9a5604138-de_DE-1',
        'name' => 'Modifying Team'
    ],
    'parent' => [],
    'query' => [
        '@type' => 'Honeybee\Infrastructure\DataAccess\Query\Query',
        'search_criteria_list' => [],
        'filter_criteria_list' => [
            [
                '@type' => 'Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria',
                'attribute_path' => 'materialized_path',
                'comparison' => [
                    '@type' => 'Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals',
                    'comparator' => 'eq',
                    'comparand' => 'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
                    'inverted' => false
                ]
            ]
        ],
        'sort_criteria_list' => [],
        'offset' => 0,
        'limit' => 10000
    ],
    'projections' => [
        [
            '@type' => 'honeybee-tests.game_schema.team',
            'identifier' => 'honeybee.fixtures.team-abeca70c-c0d9-4d6d-a983-1441d7343954-de_DE-1',
            'revision' => 2,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:52:37.371793+00:00',
            'modified_at' => '2016-03-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'parent_node_id' => 'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
            'materialized_path' =>
                'honeybee.fixtures.team-2d10d19a-7aca-4d87-aa34-1ea9a5604138-de_DE-1/' .
                'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
            'name' => 'Child Team'
        ],
        [
            '@type' => 'honeybee-tests.game_schema.team',
            'identifier' => 'honeybee.fixtures.team-5ab9c99b-3d69-4cfe-8f06-1d367a02160b-de_DE-1',
            'revision' => 2,
            'uuid' => '5ab9c99b-3d69-4cfe-8f06-1d367a02160b',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:53:37.371793+00:00',
            'modified_at' => '2016-03-27T10:53:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'parent_node_id' => 'honeybee.fixtures.team-abeca70c-c0d9-4d6d-a983-1441d7343954-de_DE-1',
            'materialized_path' =>
                'honeybee.fixtures.team-2d10d19a-7aca-4d87-aa34-1ea9a5604138-de_DE-1/' .
                'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1/' .
                'honeybee.fixtures.team-abeca70c-c0d9-4d6d-a983-1441d7343954-de_DE-1',
            'name' => 'Grand Child Team'
        ]
    ],
    'expectations' => [
        [
            '@type' => 'honeybee-tests.game_schema.team',
            'identifier' => 'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
            'revision' => 3,
            'uuid' => 'd8668418-719e-4c09-886c-c49f45d3ee97',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-26T10:52:37.371793+00:00',
            'modified_at' => '2016-04-28T10:54:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'parent_node_id' => '',
            'materialized_path' => '',
            'name' => 'Modifying Team'
        ],
        [
            '@type' => 'honeybee-tests.game_schema.team',
            'identifier' => 'honeybee.fixtures.team-abeca70c-c0d9-4d6d-a983-1441d7343954-de_DE-1',
            'revision' => 2,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:52:37.371793+00:00',
            'modified_at' => '2016-03-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'parent_node_id' => 'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
            'materialized_path' => 'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1',
            'name' => 'Child Team'
        ],
        [
            '@type' => 'honeybee-tests.game_schema.team',
            'identifier' => 'honeybee.fixtures.team-5ab9c99b-3d69-4cfe-8f06-1d367a02160b-de_DE-1',
            'revision' => 2,
            'uuid' => '5ab9c99b-3d69-4cfe-8f06-1d367a02160b',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:53:37.371793+00:00',
            'modified_at' => '2016-03-27T10:53:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'parent_node_id' => 'honeybee.fixtures.team-abeca70c-c0d9-4d6d-a983-1441d7343954-de_DE-1',
            'materialized_path' =>
                'honeybee.fixtures.team-d8668418-719e-4c09-886c-c49f45d3ee97-de_DE-1/' .
                'honeybee.fixtures.team-abeca70c-c0d9-4d6d-a983-1441d7343954-de_DE-1',
            'name' => 'Grand Child Team'
        ]
    ]
];
