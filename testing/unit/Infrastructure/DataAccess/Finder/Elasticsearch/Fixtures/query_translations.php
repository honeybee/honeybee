<?php

use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;

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
                'sort' => [[ 'created_at' => [ 'order' => 'asc'] ]]
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
                'sort' => [[ 'created_at' => [ 'order' => 'asc' ]]]
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
                'sort' => [[ 'created_at' => [ 'order' => 'asc' ]]]
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
                'sort' => [[ 'created_at' => [ 'order' => 'asc' ]]]
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
                                        [ 'term' => [ 'friends.referenced_identifier' => 'honeybee-system_account-user-123' ] ]
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
                'sort' => [[ 'created_at' => [ 'order' => 'asc' ]]]
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
    ]
];
