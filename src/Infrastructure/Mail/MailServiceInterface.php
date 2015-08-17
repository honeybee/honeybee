<?php

namespace Honeybee\Infrastructure\Mail;

/**
 * Interface that represents a simplified mailer.
 */
interface MailServiceInterface
{
    /**
     * Key for 'number of sent mails' in return array of the send method.
     */
    const SENT_MAILS = 'sent_mails';

    /**
     * Key for 'failed recipient addresses' in return array of the send method.
     */
    const FAILED_RECIPIENTS = 'failed_recipients';

    /**
     * Key name for the default mailer settings (one of the mailers is the default one)
     */
    const DEFAULT_MAILER_NAME = 'default_mailer';

    /**
     * Key name for all mailer settings (under which all named mailers are stored)
     */
    const DEFAULT_MAILERS_KEY = 'mailers';

    /**
     * Sends the given message instance via the configured mailer and transport.
     *
     * @param MailInterface $mail message to send
     * @param string $mailer_config_name mailer config to use (for overrides etc.)
     *
     * @return array with 'sent_mails' (number of mails sent) and 'failed_recipients' (rejected email addresses)
     */
    public function send(MailInterface $message, $mailer_config_name = null);

    /**
     * Returns the internally used mailer instance to allow for more advanced
     * use cases where the simple MailInterface is not sufficient at all.
     *
     * @return mixed concrete mailer instance used
     */
    public function getMailer();
}
