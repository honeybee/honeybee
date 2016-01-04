<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\SwiftMailer;

use Exception;
use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Honeybee\Infrastructure\DataAccess\Connector\Status;
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
    protected function connect()
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

    /**
     * @return Status of this connector
     */
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        try {
            $this->getConnection();
            return Status::working($this);
        } catch (Exception $e) {
            error_log('[' . static::CLASS . '] Null mailer connection failed: ' . $e->getTraceAsString());
            return Status::failing($this, [ 'message' => 'Exception on creating the mailer: ' . $e->getMessage() ]);
        }
    }
}
