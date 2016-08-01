<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\RabbitMq;

use Honeybee\Infrastructure\DataAccess\Storage\Storage;

abstract class RabbitMqStorage extends Storage
{
    protected function getExchangeBindings()
    {
        $vhost = $this->getConfig()->get('vhost', '%2f');
        $exchange = $this->getConfig()->get('exchange');

        $endpoint = "/api/exchanges/$vhost/$exchange/bindings/source";
        return (array)$this->connector->getFromAdminApi($endpoint);
    }
}
