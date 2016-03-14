<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use ZMQ;
use ZMQContext;

class ZmqTransport extends EventTransport
{
    protected $host;

    protected $port;

    protected $channel;

    protected $socket;

    public function __construct($name, $host, $port, $channel)
    {
        parent::__construct($name);

        $this->host = $host;
        $this->port = $port;
        $this->channel = $channel;
    }

    public function send($channel_name, EventInterface $event, $subscription_index, SettingsInterface $settings = null)
    {
        $this->getSocket()->send(
            json_encode(
                array(
                    'event' => $event->toArray(),
                    'channel' => $this->channel,
                    'event_channel' => $channel_name,
                    'subscription_index' => $subscription_index
                )
            )
        );
    }

    // @todo inject zmq context via constructor and provision via di-container
    protected function getSocket()
    {
        if (!$this->socket) {
            $context = new ZMQContext();
            $this->socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'org.honeybee.events');
            $this->socket->connect(
                sprintf("tcp://%s:%s", $this->host, $this->port)
            );
        }

        return $this->socket;
    }
}
