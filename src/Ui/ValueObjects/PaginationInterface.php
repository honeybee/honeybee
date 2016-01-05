<?php

namespace Honeybee\Ui\ValueObjects;

interface PaginationInterface
{
    const DEFAULT_NUMBER_OF_RESULTS = 0;
    const DEFAULT_LIMIT_PER_PAGE = 25;
    const DEFAULT_OFFSET = 0;

    public static function createByOffset(
        $number_of_results = self::DEFAULT_NUMBER_OF_RESULTS,
        $limit_per_page = self::DEFAULT_LIMIT_PER_PAGE,
        $offset = self::DEFAULT_OFFSET
    );

    public function toArray();

    public function getNumberOfPages();
    public function getLimitPerPage();

    public function getNumberOfResults();
    public function getLimit();
    public function getOffset();

    public function getCurrentPageOffset();
    public function getCurrentPageNumber();

    public function hasPrevPage();
    public function getPrevPageOffset();
    public function getPrevPageNumber();

    public function hasNextPage();
    public function getNextPageOffset();
    public function getNextPageNumber();

    public function isFirstPage();
    public function getFirstPageOffset();
    public function getFirstPageNumber();

    public function isLastPage();
    public function getLastPageOffset();
    public function getLastPageNumber();
}
