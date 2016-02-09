<?php

use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Infrastructure\DataAccess\Query\RangeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\LessThan;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\GreaterThan;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\GreaterThanOrEquals;

return [
    //
    // "match_all" query, that is filtered by a single attribute criteria.
    //
    [
        'query' => new Query(
            new CriteriaList,
            new CriteriaList([ new AttributeCriteria('username', 'honeybee-tester') ]),
            new CriteriaList([ new SortCriteria('created_at') ]),
            0,
            100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'and' => [
                                [ 'term' => [ 'username.filter' => 'honeybee-tester' ] ]
                            ]
                        ]
                    ]
                ],
                'sort' => [ [ 'created_at' => [ 'order' => 'asc', 'unmapped_type' => 'date' ] ] ]
            ],
            'size' => 100,
            'from' => 0
        ]
    ],
    //
    // "match_all" query, that is filtered by several attribute criterias using "and" to chain them.
    //
    [
        'query' => new Query(
            new CriteriaList,
            new CriteriaList(
                [
                    new AttributeCriteria('username', 'honeybee-tester'),
                    new AttributeCriteria('friends.referenced_identifier', 'honeybee-system_account-user-123')
                ]
            ),
            new CriteriaList([ new SortCriteria('created_at') ]),
            0,
            100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'and' => [
                                [ 'term' => [ 'username.filter' => 'honeybee-tester' ] ],
                                [ 'term' => [ 'friends.referenced_identifier' => 'honeybee-system_account-user-123' ] ]
                            ]
                        ]
                    ]
                ],
                'sort' => [ [ 'created_at' => [ 'order' => 'asc', 'unmapped_type' => 'date' ] ] ]
            ],
            'size' => 100,
            'from' => 0
        ]
    ],
    //
    // "match_all" query, that is filtered by several attribute criterias using "or" to chain them.
    //
    [
        'query' => new Query(
            new CriteriaList,
            new CriteriaList(
                [
                    new AttributeCriteria('username', 'honeybee-tester'),
                    new AttributeCriteria('friends.referenced_identifier', 'honeybee-system_account-user-123')
                ],
                CriteriaList::OP_OR
            ),
            new CriteriaList([ new SortCriteria('created_at') ]),
            0,
            100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'or' => [
                                [ 'term' => [ 'username.filter' => 'honeybee-tester' ] ],
                                [ 'term' => [ 'friends.referenced_identifier' => 'honeybee-system_account-user-123' ] ]
                            ]
                        ]
                    ]
                ],
                'sort' => [ [ 'created_at' => [ 'order' => 'asc', 'unmapped_type' => 'date' ] ] ]
            ],
            'size' => 100,
            'from' => 0
        ]
    ],
    //
    // search for foobar.
    //
    [
        'query' => new Query(
            new CriteriaList([ new SearchCriteria('foobar') ]),
            new CriteriaList,
            new CriteriaList([ new SortCriteria('created_at') ]),
            0,
            100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'match' => [ '_all' => [ 'query' => 'foobar', 'type' => 'phrase_prefix' ] ]
                ],
                'sort' => [ [ 'created_at' => [ 'order' => 'asc', 'unmapped_type' => 'date' ] ] ]
            ],
            'size' => 100,
            'from' => 0
        ]
    ],
    //
    // "match_all" query, that is filtered by several attribute criterias using "and" and "or" to chain them.
    //
    [
        'query' => new Query(
            new CriteriaList,
            new CriteriaList(
                [
                    new AttributeCriteria('workflow_state', 'deleted'),
                    new CriteriaList(
                        [
                            new AttributeCriteria('username', 'honeybee-tester'),
                            new AttributeCriteria('friends.referenced_identifier', 'honeybee-system_account-user-123')
                        ],
                        CriteriaList::OP_OR
                    ),
                    new CriteriaList(
                        [
                            new AttributeCriteria('username', 'honeybee-tester'),
                        ],
                        CriteriaList::OP_AND
                    )
                ]
            ),
            new CriteriaList([ new SortCriteria('created_at') ]),
            0,
            100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'and' => [
                                [ 'term' => [ 'workflow_state' => 'deleted' ] ],
                                [
                                    'or' => [
                                        [ 'term' => [ 'username.filter' => 'honeybee-tester' ] ],
                                        [ 'term' =>
                                            [
                                                'friends.referenced_identifier' => 'honeybee-system_account-user-123'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'and' => [
                                        [ 'term' => [ 'username.filter' => 'honeybee-tester' ] ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => [ [ 'created_at' => [ 'order' => 'asc', 'unmapped_type' => 'date' ] ] ]
            ],
            'size' => 100,
            'from' => 0
        ]
    ],
    //
    // "match_all" query, that is filtered by several attribute criterias with empty nested list.
    //
    [
        'query' => new Query(
            new CriteriaList,
            new CriteriaList(
                [
                    new AttributeCriteria('workflow_state', 'deleted'),
                    new CriteriaList([], CriteriaList::OP_OR)
                ]
            ),
            new CriteriaList,
            0,
            100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'and' => [
                                [ 'term' => [ 'workflow_state' => 'deleted' ] ],
                            ]
                        ]
                    ]
                ],
                'sort' => []
            ],
            'size' => 100,
            'from' => 0
        ]
    ],
    //
    // "match_all" query with no criteria
    //
    [
        'query' => new Query(
            new CriteriaList,
            new CriteriaList,
            new CriteriaList,
            0,
            100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'match_all' => []
                ],
                'sort' => []
            ],
            'size' => 100,
            'from' => 0
        ]
    ],
    //
    // "match_all" with "range" filter
    //
    [
        'query' => new Query(
            new CriteriaList,
            new CriteriaList(
                [
                    new RangeCriteria('created_at', new LessThan('2016-03-02')),
                    new RangeCriteria('modified_at', new GreaterThanOrEquals('2016-03-02'))
                ]
            ),
            new CriteriaList,
            50,
            1000
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'and' => [
                                [
                                    'range' => [
                                        'created_at' => [ 'lt' => '2016-03-02' ]
                                     ]
                                ],
                                [
                                    'range' => [
                                        'modified_at' => [ 'gte' => '2016-03-02' ]
                                     ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => []
            ],
            'size' => 1000,
            'from' => 50
        ]
    ],
    //
    // "match_all" with multiple "range" filters on same attribute
    //
    [
        'query' => new Query(
                new CriteriaList,
                new CriteriaList(
                    [
                        new RangeCriteria(
                            'created_at',
                            new GreaterThan('2016-02-02'),
                            new LessThan('2016-03-02')
                        )
                    ]
                ),
                new CriteriaList,
                0,
                100
        ),
        'expected_es_query' => [
            'index' => 'honeybee-system_account',
            'type' => 'user',
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'and' => [
                                [
                                    'range' => [
                                        'created_at' => [
                                            'gt' => '2016-02-02',
                                            'lt' => '2016-03-02'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => []
            ],
            'size' => 100,
            'from' => 0
        ]
    ]
];
