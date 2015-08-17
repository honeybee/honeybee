<?php

namespace Honeybee\Infrastructure\DataAccess\UnitOfWork;

interface UnitOfWorkInterface
{
    public function create();

    public function checkout($aggregate_root_id);

    public function commit();

    public function rollback();
}
