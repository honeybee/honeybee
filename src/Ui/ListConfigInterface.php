<?php

namespace Honeybee\Ui;

interface ListConfigInterface
{
    public function asQuery();

    public function getFilter();
    public function hasFilter();

    public function getLimit();
    public function hasLimit();

    public function getOffset();
    public function hasOffset();

    public function getSearch();
    public function hasSearch();

    public function getSort();
    public function hasSort();

    public function getSettings();
}
