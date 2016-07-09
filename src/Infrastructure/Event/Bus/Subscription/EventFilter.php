<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Expression\ExpressionServiceInterface;
use Psr\Log\LoggerInterface;

class EventFilter implements EventFilterInterface
{
    protected $settings;

    protected $expression_service;

    protected $logger;

    public function __construct(
        Settings $settings,
        ExpressionServiceInterface $expression_service,
        LoggerInterface $logger
    ) {
        $this->settings = $settings;
        $this->expression_service = $expression_service;
        $this->logger = $logger;
    }

    public function accept(EventInterface $event)
    {
        $expression = $this->settings->get('expression', false);
        if (!$expression) {
            throw new RuntimeError("Missing required 'expression' setting.");
        }

        return $this->expression_service->evaluate($expression, [ 'event' => $event ]);
    }

    public function __toString()
    {
        return $this->settings->get('expression', false);
    }
}
