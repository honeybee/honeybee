<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\SwiftMailer;

use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Swift_Mailer;
use Swift_SendmailTransport;

/**
 * Connection is a Swift_Mailer with a Swift_SendmailTransport for local MTAs.
 */
class LocalSendmailConnector extends Connector
{
    const DEFAULT_COMMAND = '/usr/sbin/sendmail -bs';

    protected $mailer;

    protected $transport;

    /**
     * @return Swift_Mailer with a Swift_SendmailTransport
     */
    protected function connect()
    {
        $this->transport = Swift_SendmailTransport::newInstance(
            $this->config->get('command', self::DEFAULT_COMMAND)
        );

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
