<?php

namespace Honeybee\Infrastructure\Mail;

/**
 * Interface that represents a simplified email (mime) message.
 *
 * It is possible to add multiple parts to a mime message, but this interface
 * only defines a simplified subset with a HTML and TEXT body. A concrete
 * implementation may add methods like addPart($content, $content_type) or give
 * access to advanced features like using embedded images etc.
 */
interface MailInterface
{
    /**
     * @see RFC 2183 Content-Disposition: inline
     */
    const CONTENT_DISPOSITION_INLINE = 'inline';

    /**
     * @see RFC 2183 Content-Disposition: attachment
     */
    const CONTENT_DISPOSITION_ATTACHMENT = 'attachment';

    /**
     * @param string $subject subject of the message
     */
    public function setSubject($subject);

    /**
     * @return string subject of the message
     */
    public function getSubject($default = null);

    /**
     * Sets a "text/html" main body part for this message.
     *
     * @param string $html html content as string for the html body part of the message
     */
    public function setBodyHtml($html);

    /**
     * @return string of html content for the message
     */
    public function getBodyHtml($default = null);

    /**
     * Sets a "text/plain" main body part for this message.
     *
     * It is suggested to set the alternative text only content if the html body
     * is set to provide accessible text for non-html mail clients.
     *
     * @param string $text plain text string for the alternative or main body part of the message
     */
    public function setBodyText($text);

    /**
     * @return string of plain text alternative content for the message
     */
    public function getBodyText($default = null);

    /**
     * Adds the given data as a file attachment to the message. This is useful
     * if there's data that has not been written to disk but should be added as
     * an attachment anyways.
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
     * @param mixed $content data of the file, may be binary,
     *                  text or even data uri depending on support in implementation
     * @param string $name filename to use for content
     * @param string $content_type mime type of content, e.g. "application/pdf"
     * @param string $content_disposition defines whether the attachment should be inline or attached
     */
    public function addAttachment($content, $name, $content_type, $content_disposition);

    /**
     * Adds the given file as an embedded or attached file to the message. This
     * is a convenience wrapper for addAttachment() to allow file paths instead
     * of concrete data content from memory.
     *
     * For the content_disposition parameter the constants CONTENT_DISPOSITION_INLINE
     * (to be referenced in other parts of the message) or CONTENT_DISPOSITION_ATTACHMENT
     * should be used.
     *
     * @param string $path path to the file to attach
     * @param string $name filename to use for the file; defaults to basename of the given file path
     * @param string $content_type mime type of content, e.g. "application/pdf"; defaults to "application/octet-stream"
     * @param string $content_disposition defines whether the attachment should be inline or attached;
     *                  defaults to CONTENT_DISPOSITION_ATTACHMENT
     */
    public function addFile($path, $name, $content_type, $content_disposition);

    /**
     * @return array[] of attachments with each attachment array having the following keys:
     *                  content, name, content_type and content_disposition
     */
    public function getAttachments();

    /**
     * Set the sender of this message.
     *
     * If multiple addresses are present in the From field, this SHOULD be set
     * as according to RFC 2822 it is a requirement when there are multiple
     * From addresses.
     *
     * The sender has a higher significance than the From address.
     *
     * @param mixed $address string with email address
     */
    public function setSender($address);

    /**
     * @return string[] sender address for this message as associative array (with key being the email address)
     */
    public function getSender($default = array());

    /**
     * Set the From address of this message.
     *
     * When multiple From addresses are used, you should set the Sender address
     * (according to RFC 2822 must set the sender address).
     *
     * @param mixed $addresses string with email address(es)
     *              or multiple element associative array with email addresses and their display name
     *              (e.g. array('email@example.com' => 'Some Name', 'email2@example.com' => 'Some Other Name'))
     */
    public function setFrom($addresses);

    /**
     * @return string[] From address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getFrom($default = array());

    /**
     * Set the To address(es) - recipients set in this field will receive a
     * copy of this message.
     *
     * @param mixed $addresses string with email address(es)
     */
    public function setTo($addresses);

    /**
     * @return string[] To address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getTo($default = array());

    /**
     * Set the Cc address(es) - recipients set in this field will receive a
     * 'carbon-copy' of this message.
     *
     * @param mixed $addresses string with email address(es)
     */
    public function setCc($addresses);

    /**
     * @return string[] Cc address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getCc($default = array());

    /**
     * Set the Bcc address(es) - recipients set in this field will receive a
     * 'blind-carbon-copy' of this message. That is, they will get the message,
     * but any other recipients of the message will have no such knowledge of
     * their receipt of it.
     *
     * @param mixed $addresses string with email address(es)
     */
    public function setBcc($addresses);

    /**
     * @return string[] Bcc address(es) of this message as associative array (with keys being the email addresses)
     */
    public function getBcc($default = array());

    /**
     * Set the Reply-To address(es) - any replies from the receiver will be
     * sent to this address.
     *
     * @param mixed $addresses string with email address(es)
     */
    public function setReplyTo($addresses);

    /**
     * @return string[] Reply-To address(es) of this message as associative array
     */
    public function getReplyTo($default = array());

    /**
     * @param string $address return-path address for bounce handling as string
     */
    public function setReturnPath($address);

    /**
     * @return string[] return-path address for bounce handling (with key being the email address)
     */
    public function getReturnPath($default = array());

    /**
     * @param integer $date origination date of the message as a UNIX timestamp
     */
    public function setDate($date);

    /**
     * @return integer origination date of the message as a UNIX timestamp
     */
    public function getDate($default = null);
}
