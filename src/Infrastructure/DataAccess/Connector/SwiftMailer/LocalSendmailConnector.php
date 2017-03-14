<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\SwiftMailer;

use Exception;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Honeybee\Infrastructure\DataAccess\Connector\Status;
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

    /**
     * @return Status of this connector
     */
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        try {
            $transport = Swift_SendmailTransport::newInstance(
                $this->config->get('command', self::DEFAULT_COMMAND)
            );
            $mailer = Swift_Mailer::newInstance($transport);

            $mailer->getTransport()->start();

            $noop_response = $mailer->getTransport()->executeCommand("NOOP\r\n", [ 250 ]);
            $helo_response = $mailer->getTransport()->executeCommand("HELO statustest\r\n", [ 250 ]);

            if (!$mailer->getTransport()->isStarted()) {
                return Status::failing($this, [
                    'message' => 'Unknown problem as transport is not started.'
                ]);
            }

            return Status::working($this, [
                'noop_response' => $noop_response,
                'helo_response' => $helo_response
            ]);
        } catch (Exception $e) {
            error_log(
                sprintf(
                    "[%s] Starting mailer transport failed: %s\n%s",
                    static::CLASS,
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
            return Status::failing($this, [
                'message' => 'Exception on starting mailer transport: ' . $e->getMessage()
            ]);
        }
    }
}
