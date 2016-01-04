<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\SwiftMailer;

use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Swift_Mailer;
use Swift_SmtpTransport;

/**
 * Connection is a Swift_Mailer with a Swift_SmtpTransport.
 *
 * Supported settings:
 * - username
 * - password
 * - host (try 'smtp.gmail.com')
 * - port (defaults to 465)
 * - encryption (defaults to 'tls')
 * - auth_mode (defaults to 'login')
 * - timeout
 * - local_domain (fqdn truly used, w/o domain name use the IP like this: [127.0.0.1])
 * - source_ip (used to connect to the destination)
 */
class SmtpConnector extends Connector
{
    const DEFAULT_SMTP_PORT = 25;
    const DEFAULT_SMTP_SSL_PORT = 465;
    const DEFAULT_ENCRYPTION = 'tls';
    const DEFAULT_AUTH_MODE = 'login';

    protected $mailer;

    protected $transport;

    /**
     * @return Swift_Mailer with a Swift_SmptTransport
     */
    protected function connect()
    {
        $this->needs('host')->needs('username')->needs('password');

        $this->transport = Swift_SmtpTransport::newInstance();

        $this->transport->setHost($this->config->get('host'));
        $this->transport->setPort($this->config->get('port', self::DEFAULT_SMTP_SSL_PORT));
        $this->transport->setEncryption($this->config->get('encryption', self::DEFAULT_ENCRYPTION));
        $this->transport->setAuthMode($this->config->get('auth_mode', self::DEFAULT_AUTH_MODE));
        $this->transport->setUsername($this->config->get('username'));
        $this->transport->setPassword($this->config->get('password'));

        if ($this->config->has('local_domain')) {
            $this->transport->setLocalDomain($this->config->get('local_domain'));
        }

        if ($this->config->has('timeout')) {
            $this->transport->setTimeout($this->config->get('timeout'));
        }

        if ($this->config->has('source_ip')) {
            $this->transport->setSourceIp($this->config->get('source_ip'));
        }

        $this->mailer = Swift_Mailer::newInstance($this->transport);

        return $this->mailer;
    }

    /**
     * @return array with available stream transport names (e.g. 'tls', 'ssl', 'tlsv1.2')
     */
    public static function getAvailableStreamTransports()
    {
        return stream_get_transports();
    }

    public function getTransport()
    {
        return $this->transport;
    }

    public function getMailer()
    {
        return $this->mailer;
    }
}
