<?php

namespace Honeybee\Infrastructure\Mail;

/**
 * Interface that represents a simplified mail message (without having
 * to know the MIME internals).
 */
class Message implements MailInterface
{
    /**
     * Set the 'type' key of attachments to specify that the content of the
     * 'content' key is raw data.
     */
    const ATTACHMENT_TYPE_DATA = 'data';

    /**
     * Set the 'type' key of attachments to specify that the content of the
     * 'content' key is a path to a file.
     */
    const ATTACHMENT_TYPE_PATH = 'path';

    /**
     * Set the 'type' key of attachments to specify that the content of the
     * 'content' key is a path to a resource (e.g. file handle).
     */
    const ATTACHMENT_TYPE_RESOURCE = 'resource';

    /**
     * Default mime type that is used when no content type is specified for
     * attachments.
     */
    const CONTENT_TYPE_DEFAULT = "application/octet-stream";

    protected $bcc;
    protected $body_html;
    protected $body_text;
    protected $cc;
    protected $date;
    protected $from;
    protected $reply_to;
    protected $return_path;
    protected $sender;
    protected $subject;
    protected $to;
    protected $attachments;

    /**
     * Set this to true if validation should silently ignore invalid email addresses instead of throwing exceptions
     *
     * @var boolean
     */
    protected $silent = false;

    /**
     * Create new simplified email message.
     *
     * @todo implement value setting via constructor
     *
     * @param boolean $silent
     */
    public function __construct($silent = false)
    {
        $this->silent = $silent;

        $this->bcc = array();
        $this->body_html = null;
        $this->body_text = null;
        $this->cc = array();
        $this->date = null;
        $this->from = array();
        $this->reply_to = array();
        $this->return_path = array();
        $this->sender = array();
        $this->subject = null;
        $this->to = array();
        $this->attachments = array();
    }

    /**
     * Adds the given data as a file attachment to the message.
     *
     * Implementations should declare what type of content they support.
     * Convenience methods like addInlineAttachment() or addFile($path)
     * may of course be defined by concrete implementation of this interface.
     *
     * For the content_disposition parameter the constants
     * - CONTENT_DISPOSITION_INLINE (to be referenced in other parts) or
     * - CONTENT_DISPOSITION_ATTACHMENT
     * should be used.
     *
     * @param mixed $content data of the file or a file resource handle
     * @param string $name filename to use for content
     * @param string $content_type mime type of content, e.g. "application/pdf"; defaults to "application/octet-stream"
     * @param string $content_disposition defines whether the attachment should be inline or attached;
     *                  defaults to CONTENT_DISPOSITION_ATTACHMENT
     *
     * @return Message instance for fluent API support
     */
    public function addAttachment($content, $name, $content_type, $content_disposition)
    {
        $this->attachments[] = $this->createAttachment(
            $content,
            $name,
            $content_type,
            $content_disposition,
            self::ATTACHMENT_TYPE_DATA
        );

        return $this;
    }

    /**
     * Adds the given file as an embedded or attached file to the message.
     *
     * For the content_disposition parameter the constants
     * - CONTENT_DISPOSITION_INLINE (to be referenced in other parts) or
     * - CONTENT_DISPOSITION_ATTACHMENT
     * should be used.
     *
     * @param string $path path to the file to attach
     * @param string $name filename to use for the file; defaults to basename of the given file path
     * @param string $content_type mime type of content, e.g. "application/pdf"; defaults to "application/octet-stream"
     * @param string $content_disposition defines whether the attachment should be inline or attached;
     *                  defaults to CONTENT_DISPOSITION_ATTACHMENT
     *
     * @return Message instance for fluent API support
     *
     * @throws MessageConfigurationException if given path is not readable
     */
    public function addFile($path, $name, $content_type, $content_disposition)
    {
        if (!empty($path)) {
            $path = trim($path);
        }

        if (!is_readable($path)) {
            throw new MessageConfigurationException('Given path to file to attach is not readable: ' . $path);
        }

        if (empty($name)) {
            $name = basename($path);
        }

        $this->attachments[] = $this->createAttachment(
            $path,
            $name,
            $content_type,
            $content_disposition,
            self::ATTACHMENT_TYPE_PATH
        );

        return $this;
    }

    /**
     * @return array[] of attachments with each attachment array having the following keys:
     *              content, name, content_type and content_disposition
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return string[] Bcc address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getBcc($default = null)
    {
        if (empty($this->bcc) && $default !== null) {
            return $this->getConsolidated($default);
        }

        return $this->bcc;
    }

    /**
     * @return string html body content for this message
     */
    public function getBodyHtml($default = null)
    {
        if (empty($this->body_html) && $default !== null) {
            return (string)$default;
        }

        return $this->body_html;
    }

    /**
     * @return string plain text body content for this message
     */
    public function getBodyText($default = null)
    {
        if (empty($this->body_text) && $default !== null) {
            return (string)$default;
        }

        return $this->body_text;
    }

    /**
     * @return string[] Cc address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getCc($default = null)
    {
        if (empty($this->cc) && $default !== null) {
            return $this->getConsolidated($default);
        }

        return $this->cc;
    }

    /**
     * @return int origination date of the message as a UNIX timestamp
     */
    public function getDate($default = null)
    {
        if (empty($this->date) && $default !== null) {
            return $this->getUnixTimestamp($default);
        }

        return $this->date;
    }

    /**
     * @return string[] From address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getFrom($default = null)
    {
        if (empty($this->from) && $default !== null) {
            return $this->getConsolidated($default);
        }

        return $this->from;
    }

    /**
     * @return string[] Reply-To address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getReplyTo($default = null)
    {
        if (empty($this->reply_to) && $default !== null) {
            return $this->getConsolidated($default);
        }

        return $this->reply_to;
    }

    /**
     * @return string return-path address for bounce handling (with key being the email address)
     */
    public function getReturnPath($default = null)
    {
        if (empty($this->return_path) && $default !== null) {
            return $this->getSingleConsolidated($default);
        }

        return $this->return_path;
    }

    /**
     * @return string sender address for this message as associative array (with key being the email address)
     */
    public function getSender($default = null)
    {
        if (empty($this->sender) && $default !== null) {
            return $this->getSingleConsolidated($default);
        }

        return $this->sender;
    }

    /**
     * @return string subject of the message
     */
    public function getSubject($default = null)
    {
        if (empty($this->subject) && $default !== null) {
            return (string)$default;
        }

        return $this->subject;
    }

    /**
     * @return string[] To address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getTo($default = null)
    {
        if (empty($this->to) && $default !== null) {
            return $this->getConsolidated($default);
        }

        return $this->to;
    }

    /**
     * Set the Bcc address(es) - recipients set in this field will receive a
     * 'blind-carbon-copy' of this message. That is, they will get the message,
     * but any other recipients of the message will have no such knowledge of
     * their receipt of it.
     *
     * @param mixed $addresses string with email address(es) or multiple element associative array with email addresses
     *                  and their display name (e.g. array('email@example.com' => 'Some Name', ...))
     *
     * @return Message instance for fluent API support
     */
    public function setBcc($addresses)
    {
        $this->bcc = $this->getConsolidated($addresses);

        return $this;
    }

    /**
     * Set the main html body part of the message.
     *
     * @param string $html html content of the main body part of the message
     *
     * @return Message instance for fluent API support
     */
    public function setBodyHtml($html)
    {
        $this->body_html = trim($html);

        return $this;
    }

    /**
     * Set the main text body part of the message.
     *
     * @param string $text plain text content of the alternative or main body part of the message
     *
     * @return Message instance for fluent API support
     */
    public function setBodyText($text)
    {
        $this->body_text = trim($text);

        return $this;
    }

    /**
     * Set the Cc address(es). Recipients set in this field will receive a
     * 'carbon-copy' of this message.
     *
     * @param mixed $addresses string with email address(es) or multiple element associative array with email addresses
     *                  and their display name (e.g. array('email@example.com' => 'Some Name', ...))
     *
     * @return Message instance for fluent API support
     */
    public function setCc($addresses)
    {
        $this->cc = $this->getConsolidated($addresses);

        return $this;
    }

    /**
     * @param mixed $date origination date of the message as a UNIX timestamp or strtotime() compatible string
     *
     * @return Message instance for fluent API support
     */
    public function setDate($date)
    {
        $this->date = $this->getUnixTimestamp($date);

        return $this;
    }

    /**
     * Set the From address of this message.
     *
     * If you set multiple From addresses you must set a Sender
     * as only one person can physically send a message while it
     * may have been written by multiple persons.
     *
     * @param mixed $addresses string with email address(es) or multiple element associative array with email addresses
     *                  and their display name (e.g. array('email@example.com' => 'Some Name', ...))
     *
     * @return Message instance for fluent API support
     */
    public function setFrom($addresses)
    {
        $this->from = $this->getConsolidated($addresses);

        return $this;
    }

    /**
     * Set the Reply-To address(es) - any replies from the receiver will be
     * sent to this address.
     *
     * @param mixed $addresses string with email address(es) or multiple element associative array with email addresses
     *                  and their display name (e.g. array('email@example.com' => 'Some Name', ...))
     *
     * @return Message instance for fluent API support
     */
    public function setReplyTo($addresses)
    {
        $this->reply_to = $this->getConsolidated($addresses);

        return $this;
    }

    /**
     * Sets the address bounce notifications should be sent to.
     *
     * If you give multiple addresses to this method, only the first
     * valid one will be used.
     *
     * @param string $address return-path address for bounce handling as string
     *                  or single element associative array with email address and display name
     *                  (e.g. array('email@example.com' => 'Some Name'))
     *
     * @return Message instance for fluent API support
     */
    public function setReturnPath($address)
    {
        $this->return_path = $this->getSingleConsolidated($address);

        return $this;
    }

    /**
     * Set the sender of this message.
     *
     * If you set multiple From addresses you must set a Sender
     * as only one person can physically send a message while it
     * may have been written by multiple persons. The sender has
     * a higher significance than the From address.
     *
     * If you give multiple addresses to this method, only the first
     * one will be used.
     *
     * @param string $address return-path address for bounce handling as string
     *                  or single element associative array with email address and display name
     *                  (e.g. array('email@example.com' => 'Some Name'))
     *
     * @return Message instance for fluent API support
     */
    public function setSender($address)
    {
        $this->sender = $this->getSingleConsolidated($address);

        return $this;
    }

    /**
     * Set the subject of the message.
     *
     * @param string $subject subject of the message
     *
     * @return Message instance for fluent API support
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the To address(es) - recipients set in this field will receive a
     * copy of this message.
     *
     * @param mixed $addresses string with email address(es) or multiple element associative array with email addresses
     *                  and their display name (e.g. array('email@example.com' => 'Some Name', ...))
     *
     * @return Message instance for fluent API support
     */
    public function setTo($addresses)
    {
        $this->to = $this->getConsolidated($addresses);

        return $this;
    }

    /**
     * Return the first valid email address as array.
     *
     * @param mixed $addresses string or array
     *
     * @return array with key being the email address and value being the display name (display name may be null)
     *
     * @throws MessageConfigurationException if given values (email address or display name) are not valid
     */
    protected function getSingleConsolidated($addresses)
    {
        return $this->getConsolidated($addresses, true);
    }

    /**
     * Return all valid email addresses as array.
     *
     * @param mixed $addresses string or array
     * @param boolean $only_return_first set this to true to only get the first of all valid email addresses
     *
     * @return array with keys being valid email addresses and values being display names (display name may be null)
     *
     * @throws MessageConfigurationException if given values (email address or display name) are not valid
     */
    protected function getConsolidated($addresses, $only_return_first = false)
    {
        if (empty($addresses) || (!is_string($addresses) && !is_array($addresses))) {
            if ($this->shouldIgnoreValidationErrors()) {
                return array();
            }

            throw new MessageConfigurationException(
                'The given argument is not a valid email address.' .
                'Please use either a string with a valid email address ' .
                'or give an associative array with keys being the email address and value being the display name.' .
                'You may omit the display name or set it to null if you want.'
            );
        }

        // 'email@example.com'
        if (is_string($addresses)) {
            if (self::isValidEmail($addresses)) {
                return array($addresses => null);
            } else {
                if ($this->shouldIgnoreValidationErrors()) {
                    return array();
                }

                throw new MessageConfigurationException(
                    'The given argument "' . $addresses . '"is not a valid email address.' .
                    'Please use either a string with a valid email address ' .
                    'or give an associative array with key being the email address and value being the display name.' .
                    'You may omit the display name or set it to null if you want.'
                );
            }
        }

        $valid_addresses = array();

        // array('email@example.com' => 'Display Name'...)
        foreach ($addresses as $email => $display_name) {
            // simple array('email@example.com', 'foo@bar.com'...) with numeric keys
            if (is_numeric($email) && is_string($display_name) && !empty($display_name)) {
                $email = $display_name;
                $display_name = '';
            }

            $email = trim($email);
            if (self::isValidEmail($email)) {
                $valid_addresses[$email] = (string) is_string($display_name) ? trim($display_name) : null;
            } else {
                if (!$this->shouldIgnoreValidationErrors()) {
                    throw new MessageConfigurationException(
                        'The given array has a key "' . $email . '" that is not a valid email address.'
                    );
                }
            }
        }

        // we only need one of the valid addresses
        if ($only_return_first === true && count($valid_addresses) > 1) {
            $valid_addresses = array_keys($valid_addresses);
            $valid_addresses = array_shift($valid_addresses);
            $valid_addresses = array($valid_addresses => null);
        }


        return $valid_addresses;
    }

    /**
     * @return boolean whether or not exceptions should be thrown instead of silently being ignored on validation errors
     */
    protected function shouldIgnoreValidationErrors()
    {
        return $this->silent;
    }

    /**
     * Creates an attachment array to be used internally to store attachments.
     *
     * @return array
     */
    protected function createAttachment(
        $content,
        $name,
        $content_type,
        $content_disposition,
        $type = self::ATTACHMENT_TYPE_DATA
    ) {
        if (!empty($name)) {
            $name = trim($name);
        }

        if (!empty($content_type)) {
            $content_type = trim($content_type);
        }

        if (empty($content_type)) {
            $content_type = self::CONTENT_TYPE_DEFAULT;
        }

        if (!empty($content_disposition)) {
            $content_disposition = trim($content_disposition);
        }

        if (empty($content_disposition)) {
            $content_disposition = self::CONTENT_DISPOSITION_ATTACHMENT;
        }

        $attachment = array(
            'content' => $content,
            'name' => $name,
            'content_type' => $content_type,
            'content_disposition' => $content_disposition,
            'type' => $type
        );

        return $attachment;
    }

    /**
     * Returns a valid integer unix timestamp.
     *
     * @param mixed $date strtotime compatible string or integer unix timestamp
     *
     * @return integer unix timestamp
     *
     * @throws MessageConfigurationException in case of invalid arguments
     */
    protected function getUnixTimestamp($date)
    {
        if (!is_int($date) && !is_string($date)) {
            throw new MessageConfigurationException(
                'The given date must be a unix timestamp (integer) ' .
                'or a date string that can be transformed via strtotime().'
            );
        }

        if (is_string($date)) {
            $date = strtotime($date);
            if (false === $date) {
                throw new MessageConfigurationException(
                    'The given date must be a unix timestamp (integer) ' .
                    'or a date string that can be transformed via strtotime().'
                );
            }
        }

        return $date;
    }

    /**
     * This method does not recognize emails like "user@localhost" as valid.
     * Nor does it find emails with IDN hosts being valid. If you want to use
     * e.g. umlaut domains just try to call something like
     * "idn_to_ascii(explode('@', $email)[1])" on the email first.
     *
     * Valid local part characters are: A-Za-z0-9.!#$%&'*+-/=?^_`{|}~
     *
     * @param string $email Email address to validate. This uses the FILTER_VALIDATE_EMAIL of PHP
     *
     * @return boolean true if valid. Fals otherwise.
     */
    public static function isValidEmail($email)
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
    }

    /**
     * Create a new message instance.
     *
     * @param mixed $from From address as string or array of email address => display name pairs
     * @param mixed $to To address as string or array of email address => display name pairs
     * @param string $subject subject of the message
     * @param string $body_html HTML part of the message
     * @param string $body_text plain text part of the message
     *
     * @return Message instance to customize further
     */
    public static function create($from, $to, $subject, $body_html, $body_text)
    {
        $message = new static();

        $message->setFrom($from);
        $message->setTo($to);
        $message->setSubject($subject);
        $message->setBodyHtml($body_html);
        $message->setBodyText($body_text);

        return $message;
    }

    /**
     * Returns a simple array representation of the message.
     *
     * @return array of all message properties
     */
    public function toArray()
    {
        return array(
            'from' => $this->getFrom(),
            'sender' => $this->getSender(),
            'to' => $this->getTo(),
            'cc' => $this->getCc(),
            'bcc' => $this->getBcc(),
            'reply_to' => $this->getReplyTo(),
            'return_path' => $this->getReturnPath(),
            'body_text' => $this->getBodyText(),
            'body_html' => $this->getBodyHtml(),
            'attachments' => $this->getAttachments()
        );
    }

    /**
     * Returns a simple string representation of the message.
     *
     * @return string
     */
    public function __toString()
    {
        $str = "Message:\n";
        $arr = $this->toArray();
        foreach ($arr as $key => $value) {
            $str .= $key . ' = ' . $this->getAsString($value) . "\n";
        }
        return $str;
    }

    /**
     * @param mixed $value object, array or string to create textual representation for
     *
     * @return string for the given value
     */
    protected function getAsString($value)
    {
        if (is_object($value)) {
            if (is_callable(array($value, '__toString'))) {
                return (string) $value->__toString();
            } else {
                return json_encode($value);
            }
        } elseif (is_array($value)) {
            $arr = array();
            foreach ($value as $key => $value) {
                if (null === $value) {
                    $arr[] = $key;
                } else {
                    $arr[] = '"' . $value . '" <' . $key . '>';
                }
            }

            return implode(', ', $arr);
        }

        return (string) $value;
    }
}
