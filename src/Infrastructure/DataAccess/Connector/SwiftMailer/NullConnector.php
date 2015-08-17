<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\SwiftMailer;

use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Swift_Mailer;
use Swift_NullTransport;

/**
 * Connection is a Swift_Mailer with a null transport (that doesn't send any mails).
 */
class NullConnector extends Connector
{
    protected $mailer;

    protected $transport;

    /**
     * @return Swift_Mailer
     */
    public function connect()
    {
        $this->transport = Swift_NullTransport::newInstance();

        $this->mailer = Swift_Mailer::newInstance($this->transport);

        return $this->mailer;
    }

    public function getMailer()
    {
        return $this->mailer;
    }

    public function getTransport()
    {
        return $this->transport;
    }
}
