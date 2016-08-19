<?php

namespace Honeybee\Infrastructure\Command\Bus;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Bus\Subscription\CommandSubscriptionInterface;
use Honeybee\Infrastructure\Command\Bus\Subscription\CommandSubscriptionMap;
use Honeybee\Infrastructure\Command\CommandEnricherInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Psr\Log\LoggerInterface;
use Trellis\Common\Object;

class CommandBus extends Object implements CommandBusInterface
{
    protected $subscriptions;

    protected $command_enricher;

    protected $logger;

    public function __construct(CommandEnricherInterface $command_enricher, LoggerInterface $logger)
    {
        $this->subscriptions = new CommandSubscriptionMap;
        $this->command_enricher = $command_enricher;
        $this->logger = $logger;
    }

    public function execute(CommandInterface $command)
    {
        $command_type = $command->getType();

        if (!$this->subscriptions->hasKey($command_type)) {
            $this->logger->debug(__METHOD__ . ' - No subscription found for command-type: ' . $command_type);
            return false;
        }

        $subscription = $this->subscriptions->getItem($command_type);
        $command_handler = $subscription->getCommandHandler();

        return $command_handler->execute($command);
    }

    public function post(CommandInterface $command)
    {
        $command_type = $command->getType();
        if (!$this->subscriptions->hasKey($command_type)) {
            return false;
        }

        $subscription = $this->subscriptions->getItem($command_type);
        $transport = $subscription->getCommandTransport();

        $enriched_command = $this->command_enricher->enrich($command);

        return $transport->send($enriched_command);
    }

    public function subscribe(CommandSubscriptionInterface $subscription)
    {
        $command_type = $subscription->getCommandType();
        if ($this->subscriptions->hasKey($command_type)) {
            throw new RuntimeError('Already registered subscription for command-type: ' . $command_type);
        }
        $this->subscriptions->setItem($command_type, $subscription);
    }

    public function unsubscribe(CommandSubscriptionInterface $subscription)
    {
        $command_type = $subscription->getCommandType();
        if ($this->subscriptions->hasKey($command_type)) {
            unset($this->subscriptions[$command_type]);
        }
    }

    public function getSubscriptions()
    {
        return $this->subscriptions;
    }
}
