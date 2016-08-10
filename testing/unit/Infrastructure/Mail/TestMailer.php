<?php

namespace Honeybee\Tests\Infrastructure\Mail;

use Exception;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Mail\MailInterface;
use Honeybee\Infrastructure\Mail\LoggingSwiftMailer;
use Psr\Log\NullLogger;
use Swift_Mailer;
use Swift_NullTransport;
use Swift_Preferences;

/**
 * Handles the sending of mails for a module.
 */
class TestMailer extends LoggingSwiftMailer
{
    protected $created_mails = [];

    /**
     * Overridden to create a LoggingSwiftMailer with a NullTransport Swift_Mailer instance.
     */
    public function __construct(array $mailer_configs)
    {
        Swift_Preferences::getInstance()->setCharset('utf-8');
        $connection = Swift_NullTransport::newInstance();
        $swift_mailer = Swift_Mailer::newInstance($connection);

        $configs = new ArrayConfig($mailer_configs);
        $logger = new NullLogger();

        parent::__construct($swift_mailer, $configs, $logger);

        $this->created_mails = [];
    }

    /**
     * Overridden to see what messages have been created when using the mailer.
     */
    public function createSwiftMessage(MailInterface $message, $mailer_config_name = null)
    {
        $mail = parent::createSwiftMessage($message, $mailer_config_name);

        $this->created_mails[] = $mail;

        return $mail;
    }

    /**
     * @return \Swift_Message last message that was created
     *
     * @throws \Exception is no mails have been created
     */
    public function getLastCreatedMail()
    {
        $cnt = count($this->created_mails);

        if (0 === $cnt) {
            throw new Exception('No mails have been sent.');
        }

        return $this->created_mails[$cnt - 1];
    }

    /**
     * @return array of \Swift_Message instances that have been created
     */
    public function getCreatedMails()
    {
        return $this->created_mails;
    }
}
