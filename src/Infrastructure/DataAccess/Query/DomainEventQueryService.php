<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaQuery;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;

class DomainEventQueryService extends QueryService implements DomainEventQueryServiceInterface
{
    public function findEventsByIdentifier($identifier, $offset = 0, $limit = 10000)
    {
        return $this->getFinder()->find(
            $this->getQueryTranslation()->translate(
                new CriteriaQuery(
                    new CriteriaList,
                    new CriteriaList([ new AttributeCriteria('aggregate_root_identifier', new Equals($identifier)) ]),
                    new CriteriaList([ new SortCriteria('seq_number', SortCriteria::SORT_ASC) ]),
                    $offset,
                    $limit
                )
            )
        );
    }
}
