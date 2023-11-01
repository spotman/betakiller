<?php

use Webmozart\Assert\Assert;

/**
 * Email module
 *
 * Ported from Kohana 2.2.3 Core to Kohana 3.0 module
 *
 * Updated to use Swiftmailer 4.0.4
 *
 * @package        Core
 * @author         Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license        http://kohanaphp.com/license.html
 */
class Kohana_Email
{
    /**
     * @var Swift_Mailer
     */
    protected static $mail;

    /**
     * @var array
     */
    protected static $config;

    /**
     * @var string Default From header value
     */
    protected static $sender;

    /**
     * Creates a SwiftMailer instance.
     *
     * @param null|array $config DSN connection string
     *
     * @return Swift_Mailer
     */
    private static function connect($config = null)
    {
        // Load default configuration
        ($config === null) and ($config = static::config());

        $driver  = $config['driver'];
        $options = $config['options'];

        switch ($driver) {
            case 'smtp':
                Assert::keyExists($options, 'hostname', 'SMTP hostname is missing');
                Assert::keyExists($options, 'port', 'SMTP port is missing');

                // Create SMTP Transport
                $transport = new Swift_SmtpTransport($options['hostname'], $options['port']);

                $encryption = $options['encryption'] ?? null;
                $username   = $options['username'] ?? null;
                $password   = $options['password'] ?? null;

                // Set encryption
                if ($encryption) {
                    $transport->setEncryption($encryption);
                }

                // Do authentication, if part of the DSN
                if ($username) {
                    $transport->setUsername($username);
                }

                if ($password) {
                    $transport->setPassword($password);
                }

                // Set the timeout to 5 seconds
                $transport->setTimeout($options['timeout'] ?? 5);
                break;
            case 'sendmail':
                // Create a sendmail connection
                $transport = new Swift_SendmailTransport(empty($options)
                    ? '/usr/sbin/sendmail -bs'
                    : $options);

                break;
            default:
                throw new \BetaKiller\Exception('Unknown email driver :name', [':name' => $driver]);
        }

        // Getting Sender
        static::$sender = $config['sender'];

        // Create the SwiftMailer instance
        return static::$mail = new Swift_Mailer($transport);
    }

    public static function config()
    {
        return static::$config ?: static::$config = (array)Kohana::$config->load('email');
    }

    /**
     * Send an email message.
     *
     * @param string|array $from    sender email (and name)
     * @param string|array $to      recipient email (and name), or an array of To, Cc, Bcc names
     * @param string       $subject message subject
     * @param string       $body    message body
     * @param bool         $html    send email as HTML
     * @param array|null   $attach  attach filenames
     *
     * @return int                      number of emails sent
     * @deprecated
     */
    public static function send($from, $to, string $subject, string $body, bool $html = null, array $attach = null)
    {
        // Connect to SwiftMailer
        (static::$mail === null) and self::connect();

        // @link https://toster.ru/q/48752
        static::$mail->getTransport()->start();

        // Determine the message type
        $contentType = ($html === true) ? 'text/html' : 'text/plain';

        // Create the message
        $msg = new Swift_Message($subject, $body, $contentType, 'utf-8');

        if (is_string($to)) {
            // Single recipient
            $msg->setTo($to);
        } elseif (is_array($to)) {
            if (isset($to[0], $to[1])) {
                // Create To: address set
                $to = ['to' => $to];
            }

            foreach ($to as $method => $set) {
                if (!in_array($method, ['to', 'cc', 'bcc'], true)) {
                    // Use To: by default
                    $method = 'to';
                }

                // Create method name
                $method = 'add'.ucfirst($method);

                if (is_array($set)) {
                    // Add a recipient with name
                    $msg->$method($set[0], $set[1]);
                } else {
                    // Add a recipient without name
                    $msg->$method($set);
                }
            }
        }

        $sender = static::$sender;

        // Use Sender email if no From email specified
        if (!$from) {
            $from = $sender;
        }

        // From
        if (is_string($from)) {
            // From without a name
            // Set Reply-To header email only
            $msg->setFrom($from)->setReplyTo($from);
        } elseif (is_array($from)) {
            [$fromAddress, $fromName] = $from;

            // From with a name
            // Set Reply-To header email with name
            $msg->setFrom($fromAddress, $fromName)->setReplyTo($fromAddress, $fromName);
        }

        // Sender
        if (is_string($sender)) {
            // From without a name
            $msg->setSender($sender);
        } elseif (is_array($sender)) {
            [$senderAddress, $senderName] = $sender;

            // Set Reply-To header email with name
            $msg->setSender($senderAddress, $senderName);
        }

        // Attachments
        if ($attach) {
            foreach ($attach as $item) {
                $msg->attach(Swift_Attachment::fromPath($item));
            }
        }

        $count = static::$mail->send($msg);

        // @link https://toster.ru/q/48752
        static::$mail->getTransport()->stop();

        return $count;
    }

} // End email
