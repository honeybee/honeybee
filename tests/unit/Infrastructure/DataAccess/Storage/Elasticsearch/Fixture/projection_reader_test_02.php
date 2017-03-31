<?php

return [
    'raw_result' => [
        'took'=> 4,
        'timed_out' => false,
        '_shards' => [
            'total' => 2,
            'successful' => 2,
            'failed' => 0
        ],
        'hits' => [
            'total' => 2,
            'max_score' => 1.0,
            'hits' => [
                [
                    '_index' => 'testing.honeybee_cmf-projection_fixtures_20160519222937',
                    '_type' => 'honeybee_cmf-projection_fixtures-book-standard',
                    '_id' => 'honeybee_cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    '_version' => 1,
                    '_score' => 1.0,
                    '_source' => [
                        '@type' => 'honeybee_cmf.projection_fixtures.book::projection.standard',
                        'identifier' =>
                            'honeybee_cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                        'revision' => 1,
                        'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
                        'language' => 'de_DE',
                        'version' => 1,
                        'created_at' => '2016-03-27T10:52:37.371793+00:00',
                        'modified_at' => '2016-03-27T10:52:37.371793+00:00',
                        'workflow_state' => 'edit',
                        'workflow_parameters' => [],
                        'metadata' => [],
                        'title' => 'Catch 22'
                    ]
                ],
                [
                    '_index' => 'testing.honeybee_cmf-projection_fixtures_20160519222937',
                    '_type' => 'honeybee_cmf-projection_fixtures-author-standard',
                    '_id' => 'honeybee_cmf.projection_fixtures.author-61d8da68-0d56-4b8b-b393-21f1a650d092-de_DE-1',
                    '_version' => 1,
                    '_score' => 1.0,
                    '_source' => [
                        '@type' => 'honeybee_cmf.projection_fixtures.author::projection.standard',
                        'identifier' =>
                            'honeybee_cmf.projection_fixtures.author-61d8da68-0d56-4b8b-b393-21f1a650d092-de_DE-1',
                        'revision' => 1,
                        'uuid' => '61d8da68-0d56-4b8b-b393-21f1a650d092',
                        'language' => 'de_DE',
                        'version' => 1,
                        'created_at' => '2016-03-28T10:52:37.371793+00:00',
                        'modified_at' => '2016-03-28T10:52:37.371793+00:00',
                        'workflow_state' => 'edit',
                        'workflow_parameters' => [],
                        'metadata' => [],
                        'firstname' => 'Stan',
                        'lastname' => 'Lee',
                        'email' => 'stan@lee.com'
                    ]
                ]
            ]
        ]
    ]
];
