<?php

namespace Honeybee\Infrastructure\Migration;

use Assert\Assertion;

abstract class RabbitMqMigration extends Migration
{
    const WAIT_SUFFIX = '.waiting';

    const UNROUTED_SUFFIX = '.unrouted';

    const REPUB_SUFFIX = '.repub';

    const QUEUE_SUFFIX = '.q';

    const REPUB_INTERVAL = 30000; //30 seconds

    protected function createQueue(MigrationTarget $migration_target, $exchange_name, $queue_name, $routing_key)
    {
        Assertion::string($exchange_name);
        Assertion::string($routing_key);
        Assertion::string($queue_name);

        $channel = $this->getConnection($migration_target)->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        $channel->queue_bind($queue_name, $exchange_name, $routing_key);
    }

    protected function createVersionList(MigrationTarget $migration_target, $exchange_name)
    {
        Assertion::string($exchange_name);

        $channel = $this->getConnection($migration_target)->channel();
        $channel->exchange_declare($exchange_name, 'topic', false, true, false, true);
    }

    protected function createExchangePipeline(MigrationTargetInterface $migration_target, $exchange_name)
    {
        Assertion::string($exchange_name);

        $wait_exchange_name = $exchange_name . self::WAIT_SUFFIX;
        $wait_queue_name = $wait_exchange_name . self::QUEUE_SUFFIX;
        $unrouted_exchange_name = $exchange_name . self::UNROUTED_SUFFIX;
        $unrouted_queue_name = $unrouted_exchange_name . self::QUEUE_SUFFIX;
        $repub_exchange_name = $exchange_name . self::REPUB_SUFFIX;
        $repub_queue_name = $repub_exchange_name . self::QUEUE_SUFFIX;

        $channel = $this->getConnection($migration_target)->channel();

        // Setup the default exchange and queue pipelines
        $channel->exchange_declare($unrouted_exchange_name, 'fanout', false, true, false, true); //internal
        $channel->exchange_declare($repub_exchange_name, 'fanout', false, true, false, true); //internal
        $channel->exchange_declare($wait_exchange_name, 'fanout', false, true, false);
        $channel->exchange_declare($exchange_name, 'direct', false, true, false, false, false, [
            'alternate-exchange' => [ 'S', $unrouted_exchange_name ]
        ]);
        $channel->queue_declare($wait_queue_name, false, true, false, false, false, [
            'x-dead-letter-exchange' => [ 'S', $exchange_name ]
        ]);
        $channel->queue_bind($wait_queue_name, $wait_exchange_name);
        $channel->queue_declare($unrouted_queue_name, false, true, false, false, false, [
            'x-dead-letter-exchange' => [ 'S', $repub_exchange_name ],
            'x-message-ttl' => [ 'I', self::REPUB_INTERVAL ]
        ]);
        $channel->queue_bind($unrouted_queue_name, $unrouted_exchange_name);
        $channel->queue_declare($repub_queue_name, false, true, false, false);
        $channel->queue_bind($repub_queue_name, $repub_exchange_name);

        $this->createShovel($migration_target, $repub_exchange_name, $exchange_name, $repub_queue_name);
    }

    protected function createShovel(
        MigrationTargetInterface $migration_target,
        $src_exchange_name,
        $dest_exchange_name,
        $src_queue
    ) {
        $connector = $migration_target->getTargetConnector();

        $endpoint = sprintf(
            '/api/parameters/shovel/%s/%s.shovel',
            $connector->getConfig()->get('vhost', '%2f'),
            $src_exchange_name
        );

        $body = [
            'value' => [
                'src-uri' => 'amqp://',
                'src-queue' => $src_queue,
                'dest-uri' => 'amqp://',
                'dest-exchange' => $dest_exchange_name,
                'add-forward-headers' => false,
                'ack-mode' => 'on-confirm',
                'delete-after' => 'never'
            ]
        ];

        $connector->putToAdminApi($endpoint, $body);
    }
}
