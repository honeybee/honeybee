<?php

use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\GreaterThan;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\GreaterThanOrEquals;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\In;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\LessThan;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\LessThanOrEquals;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Annulus;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Box;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Circle;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Point;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Polygon;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\RangeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SpatialCriteria;
use Honeybee\Ui\ListConfig;

return [
    // no arguments
    [
        'list_config' => new ListConfig,
        'expectation' => new Query(
            new CriteriaList,
            new CriteriaList,
            new CriteriaList([ new SortCriteria('modified_at') ]),
            0,
            50
        )
    ],
    // simple empty values
    [
        'list_config' => new ListConfig([
            'search' => '',
            'filter' => [],
            'sort' => '',
            'limit' => 20,
            'offset' => 40
        ]),
        'expectation' => new Query(
            new CriteriaList,
            new CriteriaList,
            new CriteriaList([ new SortCriteria('modified_at') ]),
            40,
            20
        )
    ],
    // basic values
    [
        'list_config' => new ListConfig([
            'search' => 'test string',
            'filter' => [ 'test_filter' => 'test_value' ],
            'sort' => 'created_at:desc',
            'limit' => 20,
            'offset' => 40
        ]),
        'expectation' => new Query(
            new CriteriaList([ new SearchCriteria('test string') ]),
            new CriteriaList([ new AttributeCriteria('test_filter', new Equals('test_value')) ]),
            new CriteriaList([ new SortCriteria('created_at', SortCriteria::SORT_DESC) ]),
            40,
            20
        )
    ],
    // multiple filter values
    [
        'list_config' => new ListConfig([
            'search' => 'test string',
            'filter' => [
                'test_filter' => 'test_value',
                'another_filter' => '!another value,yes sir'
            ],
            'sort' => 'created_at:desc',
            'limit' => 20,
            'offset' => 40
        ]),
        'expectation' => new Query(
            new CriteriaList([ new SearchCriteria('test string') ]),
            new CriteriaList([
                new AttributeCriteria('test_filter', new Equals('test_value')),
                new AttributeCriteria('another_filter', new Equals('another value', true)),
                new AttributeCriteria('another_filter', new Equals('yes sir'))
            ]),
            new CriteriaList([ new SortCriteria('created_at', SortCriteria::SORT_DESC) ]),
            40,
            20
        )
    ],
    // multiple complex single filter values
    [
        'list_config' => new ListConfig([
            'search' => 'test string',
            'filter' => [
                'range' => 'range(gt:2016-07-05,lte:2016-07-07)',
                'spatial' => 'spatial(in:circle([12.1,12.2],1mile))'
            ],
            'sort' => 'created_at:desc',
            'limit' => 20,
            'offset' => 40
        ]),
        'expectation' => new Query(
            new CriteriaList([ new SearchCriteria('test string') ]),
            new CriteriaList([
                new RangeCriteria(
                    'range',
                    new GreaterThan('2016-07-05'),
                    new LessThanOrEquals('2016-07-07')
                ),
                new SpatialCriteria(
                    'spatial',
                    new In(new Circle(new Point(12.1, 12.2), '1mile'))
                )
            ]),
            new CriteriaList([ new SortCriteria('created_at', SortCriteria::SORT_DESC) ]),
            40,
            20
        )
    ],
    // multiple complex list filter values
    [
        'list_config' => new ListConfig([
            'search' => 'test string',
            'filter' => [
                'range' => 'range(lt:2016-07-08,gte:2016-07-05),range(!eq:2016-07-06)',
                'spatial' => 'spatial(in:circle([12.1,12.2],2.5km)),spatial(!in:box([1,2],[2,1]))',
                'spatial_alt' => 'spatial(in:polygon([12.1,2.2],[1,2],[2,1])),spatial(!in:annulus([1,2.1],4,5))'
            ],
            'sort' => 'created_at:desc,modified_at:asc',
            'limit' => 20,
            'offset' => 40
        ]),
        'expectation' => new Query(
            new CriteriaList([ new SearchCriteria('test string') ]),
            new CriteriaList([
                new RangeCriteria(
                    'range',
                    new LessThan('2016-07-08'),
                    new GreaterThanOrEquals('2016-07-05')
                ),
                new RangeCriteria('range', new Equals('2016-07-06', true)),
                new SpatialCriteria(
                    'spatial',
                    new In(new Circle(new Point(12.1, 12.2), '2.5km'))
                ),
                new SpatialCriteria(
                    'spatial',
                    new In(new Box(new Point(1, 2), new Point(2, 1)), true)
                ),
                new SpatialCriteria(
                    'spatial_alt',
                    new In(new Polygon([ new Point(12.1, 2.2), new Point(1, 2), new Point(2, 1) ]))
                ),
                new SpatialCriteria(
                    'spatial_alt',
                    new In(new Annulus(new Point(1, 2.1), '4', '5'), true)
                )
            ]),
            new CriteriaList([
                new SortCriteria('created_at', SortCriteria::SORT_DESC),
                new SortCriteria('modified_at', SortCriteria::SORT_ASC)
            ]),
            40,
            20
        )
    ]
];
