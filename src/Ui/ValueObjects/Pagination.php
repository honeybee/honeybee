<?php

namespace Honeybee\Ui\ValueObjects;

use Honeybee\Infrastructure\Config\Settings;

class Pagination implements PaginationInterface
{
    protected $metadata;

    protected function __construct(array $metadata = [])
    {
        $this->metadata = new Settings($metadata);
    }

    public static function createByOffset(
        $number_of_results = self::DEFAULT_NUMBER_OF_RESULTS,
        $limit_per_page = self::DEFAULT_LIMIT_PER_PAGE,
        $offset = self::DEFAULT_OFFSET
    ) {
        // validate arguments and use sane defaults

        $number_of_results = (int)$number_of_results;
        $limit_per_page = (int)$limit_per_page;
        $offset = (int)$offset;
        $number_of_results = max(0, $number_of_results);
        $limit_per_page = max(1, $limit_per_page);
        $offset = max(0, $offset);

        // begin calculations

        $number_of_pages = (int)ceil($number_of_results / $limit_per_page);
        $number_of_pages = max(1, $number_of_pages);

        $last_page_offset = ($number_of_pages - 1) * $limit_per_page;
        $first_page_offset = 0;

        if ($offset >= $number_of_results) {
            $offset = $last_page_offset;
        }

        $current_page_number = (int)floor($offset / $limit_per_page) + 1;
        if ($current_page_number < 1) {
            $current_page_number = 1;
        }
        if (($current_page_number > 0) && ($current_page_number > $number_of_pages)) {
            $current_page_number = $number_of_pages;
        }

        $prev_page_offset = max(0, ($current_page_number - 1) * $limit_per_page - $limit_per_page);

        $next_page_offset = $current_page_number * $limit_per_page;
        if ($next_page_offset > $last_page_offset) {
            $next_page_offset = $last_page_offset;
        }

        // todo add the entries already displayed and yet to display information to the metadata? does anyone need it?
        $prev_number_of_entries = ($current_page_number - 1) * $limit_per_page;
        $entries_left_to_display = max(0, $number_of_results - $prev_number_of_entries);
        if ($entries_left_to_display === 0) {
            $next_page_offset = $offset;
        }

        $is_first_page = $current_page_number === 1;
        $is_last_page = $current_page_number === $number_of_pages;

        $has_prev_page = $current_page_number > 1;
        $has_next_page = $current_page_number < $number_of_pages;

        $prev_page_number = $has_prev_page ? max(1, $current_page_number - 1) : $current_page_number;
        $next_page_number = $has_next_page ? $current_page_number + 1 : $current_page_number;

        // return calculated values

        $metadata = [
            'number_of_pages' => $number_of_pages,
            'limit_per_page' => $limit_per_page,

            'number_of_results' => $number_of_results,
            'limit' => $limit_per_page,
            'offset' => $offset,

            'current_page_number' => $current_page_number,
            'current_page_offset' => $offset,

            'has_prev_page' => $has_prev_page,
            'prev_page_number' => $prev_page_number,
            'prev_page_offset' => $prev_page_offset,

            'has_next_page' => $has_next_page,
            'next_page_number' => $next_page_number,
            'next_page_offset' => $next_page_offset,

            'is_first_page' => $is_first_page,
            'first_page_offset' => $first_page_offset,
            'first_page_number' => 1,

            'is_last_page' => $is_last_page,
            'last_page_offset' => $last_page_offset,
            'last_page_number' => $number_of_pages,
        ];

        return new static($metadata);
    }

    public function toArray()
    {
        return $this->metadata->toArray();
    }

    public function getNumberOfResults()
    {
        return $this->metadata->get('number_of_results', self::DEFAULT_NUMBER_OF_RESULTS);
    }

    public function getNumberOfPages()
    {
        return $this->metadata->get('number_of_pages', 1);
    }

    public function getLimitPerPage()
    {
        return $this->metadata->get('limit_per_page', self::DEFAULT_LIMIT_PER_PAGE);
    }

    public function getLimit()
    {
        return $this->getLimitPerPage();
    }

    public function getOffset()
    {
        return $this->metadata->get('offset', self::DEFAULT_OFFSET);
    }

    public function getCurrentPageNumber()
    {
        return $this->metadata->get('current_page_number', 1);
    }

    public function getCurrentPageOffset()
    {
        return $this->getOffset();
    }

    public function hasNextPage()
    {
        return $this->metadata->get('has_next_page', false);
    }

    public function getNextPageNumber()
    {
        return $this->metadata->get('next_page_number', 1);
    }

    public function getNextPageOffset()
    {
        return $this->metadata->get('next_page_offset', 0);
    }

    public function hasPrevPage()
    {
        return $this->metadata->get('has_prev_page', false);
    }

    public function getPrevPageNumber()
    {
        return $this->metadata->get('prev_page_number', 1);
    }

    public function getPrevPageOffset()
    {
        return $this->metadata->get('prev_page_offset', 0);
    }

    public function isFirstPage()
    {
        return $this->metadata->get('is_first_page', true);
    }

    public function getFirstPageNumber()
    {
        return $this->metadata->get('first_page_number', 1);
    }

    public function getFirstPageOffset()
    {
        return $this->metadata->get('first_page_offset', 0);
    }

    public function isLastPage()
    {
        return $this->metadata->get('is_last_page', true);
    }

    public function getLastPageNumber()
    {
        return $this->metadata->get('last_page_number', 1);
    }

    public function getLastPageOffset()
    {
        return $this->metadata->get('last_page_offset', 0);
    }
}

