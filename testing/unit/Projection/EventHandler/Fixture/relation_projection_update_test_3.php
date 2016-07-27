<?php

/**
 * @codeCoverageIgnore
 */
return [
    'event' => [
        '@type' => 'Honeybee\Projection\Event\ProjectionUpdatedEvent',
        'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
        'projection_type' => 'honeybee-tests.topic_schema.topic_option::projection.standard',
        'iso_date' => '2016-05-28T10:52:37.371793+00:00',
        'metadata' => [],
        'projection_identifier' =>
            'honeybee-tests.topic_schema.topic_option-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
        'data' => [
            '@type' => 'honeybee-tests.topic_schema.topic_option::projection.standard',
            'identifier' => 'honeybee-tests.topic_schema.topic_option-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 5,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-05-27T10:52:37.371793+00:00',
            'modified_at' => '2016-05-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'New Topic Option'
        ]
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
                    'comparand' =>
                        'honeybee-tests.topic_schema.topic_option-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'inverted' => true
                ]
            ],
            [
                [
                    '@type' => 'Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria',
                    'attribute_path' => 'Topic_0p-tion.referenced_identifier',
                    'comparison' => [
                        '@type' => 'Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals',
                        'comparator' => 'eq',
                        'comparand' =>
                            'honeybee-tests.topic_schema.topic_option-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
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
            '@type' => 'honeybee-tests.topic_schema.topic::projection.standard',
            'identifier' => 'honeybee-tests.topic_schema.topic-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 3,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:52:35.349643+00:00',
            'modified_at' => '2016-04-28T10:52:35.349643+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Topic Name',
            'Topic_0p-tion' => [
                [
                    '@type' => 'topic_option',
                    'identifier' => '95c5ff31-8eca-41d5-95a0-0eb4ac35904b',
                    'referenced_identifier' =>
                        'honeybee-tests.topic_schema.topic_option-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'title' => 'Previous Topic Option'
                ]
            ]
        ]
    ],
    'expectations' => [
        [
            '@type' => 'honeybee-tests.topic_schema.topic::projection.standard',
            'identifier' => 'honeybee-tests.topic_schema.topic-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 3,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:52:35.349643+00:00',
            'modified_at' => '2016-04-28T10:52:35.349643+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Topic Name',
            'Topic_0p-tion' => [
                [
                    '@type' => 'topic_option',
                    'identifier' => '95c5ff31-8eca-41d5-95a0-0eb4ac35904b',
                    'referenced_identifier' =>
                        'honeybee-tests.topic_schema.topic_option-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'title' => 'New Topic Option'
                ]
            ]
        ]
    ]
];
