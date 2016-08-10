<?php

namespace Honeybee\Tests\Infrastructure\Mail;

use Honeybee\Infrastructure\Mail\Message;
use Honeybee\Infrastructure\Mail\LoggingSwiftMailer;
use Honeybee\Tests\TestCase;

class LoggingSwiftMailerTest extends TestCase
{
    public function setUp()
    {
    }

    /**
     * @expectedException Honeybee\Infrastructure\Mail\MessageConfigurationException
     * @codeCoverageIgnore
     */
    public function testSendingEmptyMessageFails()
    {
        $message = new Message();
        $this->getTestMailer()->send($message);
    }

    /**
     * @expectedException Honeybee\Infrastructure\Mail\MessageConfigurationException
     * @codeCoverageIgnore
     */
    public function testSendingMessageWithoutRecipientsFails()
    {
        $message = new Message();
        $message->setFrom('test@test.com')->setSubject('subject')->setBodyText('some text');
        $this->getTestMailer()->send($message);
    }

    /**
     * @expectedException Honeybee\Infrastructure\Mail\MessageConfigurationException
     * @codeCoverageIgnore
     */
    public function testSendingMessageWithMultiplFromButWithoutSenderFails()
    {
        $message = new Message();
        $message->setFrom(['test@test.com', 'foo@test.com'])
            ->setTo('someone@test.com')
            ->setSubject('subject')
            ->setBodyText('some text');
        $this->getTestMailer()->send($message);
    }

    public function testSuccessfulMessageCreation()
    {
        $message = new Message();
        $message->setFrom('from@example.com')
                ->setTo('to@example.com')
                ->setSender('sender@example.com')
                ->setSubject('subject')
                ->setBodyText('plain text')
                ->setBodyHtml('<h1>HTML</h1>')
                ->setCc('cc@example.com')
                ->setBcc('bcc@example.com')
                ->setReplyTo('reply_to@example.com')
                ->setReturnPath('return_path@example.com');

        $mail_service = $this->getTestMailer();
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastCreatedMail();

        $this->assertEquals(['from@example.com' => null], $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals(['to@example.com' => null], $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEquals(['cc@example.com' => null], $swift_mail->getCc(), 'CC is incorrect');
        $this->assertEquals(['bcc@example.com' => null], $swift_mail->getBcc(), 'BCC is incorrect');
        $this->assertEquals(['reply_to@example.com' => null], $swift_mail->getReplyTo(), 'REPLY_TO is incorrect');
        $this->assertEquals(['sender@example.com' => null], $swift_mail->getSender(), 'SENDER is incorrect');
        $this->assertEquals('return_path@example.com', $swift_mail->getReturnPath(), 'RETURN_PATH is incorrect');
        $this->assertEquals('subject', $swift_mail->getSubject(), 'SUBJECT is incorrect');

        $body_parts = $swift_mail->getChildren();
        $this->assertTrue(count($body_parts) === 2, 'There should be two body parts (html and plain text)');

        $content_types = [];
        $content = [];
        foreach ($body_parts as $part) {
            $content_types[] = $part->getContentType();
            $content[] = $part->getBody();
        }
        $this->assertContains('text/plain', $content_types, 'There should be a text/plain content type body part');
        $this->assertContains('text/html', $content_types, 'There should be a text/html content type body part');
        $this->assertContains('plain text', $content, 'There should be a plain text body part');
        $this->assertContains('<h1>HTML</h1>', $content, 'There should be a html body part');
    }

    public function testSuccessfulMessageCreationWithOverrideAllRecipients()
    {
        $default_settings = [
            'charset' => 'utf8',
            'override_all_recipients' => 'override_all_recipients@example.com'
        ];

        $config = [
            LoggingSwiftMailer::DEFAULT_MAILER_NAME => $default_settings,
            LoggingSwiftMailer::DEFAULT_MAILERS_KEY => [
                'default' => $default_settings
            ]
        ];
        $message = new Message();
        $message->setFrom('from@example.com')
                ->setTo('to@example.com')
                ->setCc('cc@example.com')
                ->setBcc('bcc@example.com')
                ->setSubject('subject')
                ->setBodyText('plain text')
                ->setBodyHtml('<h1>HTML</h1>')
                ->setSender('sender@example.com')
                ->setReplyTo('reply_to@example.com')
                ->setReturnPath('return_path@example.com');

        $mail_service = $this->getTestMailer($config);
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastCreatedMail();

        // the important test
        $this->assertEquals(['override_all_recipients@example.com' => null], $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEquals(['override_all_recipients@example.com' => null], $swift_mail->getCc(), 'CC is incorrect');
        $this->assertEquals(['override_all_recipients@example.com' => null], $swift_mail->getBcc(), 'BCC is incorrect');

        // the rest should not be overridden
        $this->assertEquals(['from@example.com' => null], $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals(['reply_to@example.com' => null], $swift_mail->getReplyTo(), 'REPLY_TO is incorrect');
        $this->assertEquals(['sender@example.com' => null], $swift_mail->getSender(), 'SENDER is incorrect');
        $this->assertEquals('return_path@example.com', $swift_mail->getReturnPath(), 'RETURN_PATH is incorrect');
        $this->assertEquals('subject', $swift_mail->getSubject(), 'SUBJECT is incorrect');

        $body_parts = $swift_mail->getChildren();
        $this->assertTrue(count($body_parts) === 2, 'There should be two body parts (html and plain text)');

        $content_types = [];
        $content = [];
        foreach ($body_parts as $part) {
            $content_types[] = $part->getContentType();
            $content[] = $part->getBody();
        }
        $content = implode('', $content);

        $this->assertContains('text/plain', $content_types, 'There should be a text/plain content type body part');
        $this->assertContains('text/html', $content_types, 'There should be a text/html content type body part');
        $this->assertContains('plain text', $content, 'Could not find plain text in the body content');
        $this->assertContains('HTML', $content, 'Could not find HTML in the body content');
    }

    public function testSuccessfulMessageCreationWithOverrideAllRecipientsForToFieldOnly()
    {
        $config = [
            LoggingSwiftMailer::DEFAULT_MAILER_NAME => [
                'override_all_recipients' => 'override_all_recipients@example.com'
            ],
            LoggingSwiftMailer::DEFAULT_MAILERS_KEY => [
                'default' => [
                    'override_all_recipients' => 'another_override_all_recipients@example.com'
                ]
            ]
        ];
        $message = new Message();
        $message->setFrom('from@example.com')
                ->setTo('to@example.com')
                ->setSubject('subject');

        $mail_service = $this->getTestMailer($config);
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastCreatedMail();

        // the important test
        $this->assertEquals(['override_all_recipients@example.com' => null], $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEmpty($swift_mail->getCc(), 'CC should be null');
        $this->assertEmpty($swift_mail->getBcc(), 'BCC should be null');

        // the rest should not be overridden
        $this->assertEquals(['from@example.com' => null], $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals('subject', $swift_mail->getSubject(), 'SUBJECT is incorrect');
    }

    public function testSuccessfulMessageCreationWithDefaultsFromConfig()
    {
        $default_settings = [
            'charset' => 'utf7',
            'default_subject' => 'default subject',
            'address_defaults' => [
                'from' => 'default_from@example.com',
                'to' => 'default_to@example.com',
                'bcc' => 'default_bcc@example.com'
            ],
            'address_overrides' => [
                'sender' => 'sender_override@example.com',
                'return_path' => 'return_path_override@example.com'
            ]
        ];

        $config = [
            LoggingSwiftMailer::DEFAULT_MAILER_NAME => $default_settings,
            LoggingSwiftMailer::DEFAULT_MAILERS_KEY => [
                'default' => $default_settings
            ]
        ];
        $message = new Message();
        $message->setSender('will-be-overwritten@example.com')->setReturnPath('and-this-one-too@example.com');

        $mail_service = $this->getTestMailer($config);
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastCreatedMail();

        $this->assertEquals('utf7', $swift_mail->getCharset(), 'Charset was not overridden');
        $this->assertEquals(['default_from@example.com' => null], $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals(['default_to@example.com' => null], $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEquals(['default_bcc@example.com' => null], $swift_mail->getBcc(), 'BCC is incorrect');
        $this->assertEquals(
            ['sender_override@example.com' => null],
            $swift_mail->getSender(),
            'SENDER was not overridden'
        );
        $this->assertEquals(
            'return_path_override@example.com',
            $swift_mail->getReturnPath(),
            'RETURN_PATH was not overridden'
        );
    }

    public function testSuccessfulMessageCreationWithCustomMailerConfig()
    {
        $config = [
            LoggingSwiftMailer::DEFAULT_MAILER_NAME => [
                'default_subject' => 'default subject',
            ],
            LoggingSwiftMailer::DEFAULT_MAILERS_KEY => [
                'default' => [
                    'default_subject' => 'default subject',
                ],
                'trololo' => [
                    'default_subject' => 'trololo subject'
                ]
            ]
        ];
        $message = new Message();
        $message->setSender('sender@example.com')->setTo('to@example.com');

        $mail_service = $this->getTestMailer($config);
        $mail_service->send($message, 'trololo');
        $swift_mail = $mail_service->getLastCreatedMail();

        $this->assertEquals(
            'trololo subject',
            $swift_mail->getSubject(),
            'Subject was not taken from custom mailer config'
        );
    }

    /**
     * @param mixed $config array with mailer settings
     *
     * @return \Honeybee\Tests\Infrastructure\Mail\TestMailer
     */
    protected function getTestMailer(array $config = [])
    {
        return new TestMailer($config);
    }
}
