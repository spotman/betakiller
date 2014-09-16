<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Email module
 *
 * Ported from Kohana 2.2.3 Core to Kohana 3.0 module
 * 
 * Updated to use Swiftmailer 4.0.4
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Email {

	//  instance

    /**
     * @var Swift_Mailer
     */
    protected static $mail;

    /**
     * @var Kohana_Config_Group
     */
    protected static $_config;

    /**
     * @var string Default From header value
     */
    protected static $_sender;

    /**
     * Creates a SwiftMailer instance.
     *
     * @param null|array $config DSN connection string
     * @return Swift_Mailer
     */
    public static function connect($config = NULL)
	{
		if ( ! class_exists('Swift_Mailer', FALSE))
		{
			// Load SwiftMailer
			require Kohana::find_file('vendor', 'swiftmailer/lib/swift_required');
		}

		// Load default configuration
		($config === NULL) and ($config = static::config());
		
		switch ($config['driver'])
		{
			case 'smtp':
				// Set port
				$port = empty($config['options']['port']) ? 25 : (int) $config['options']['port'];
				
				// Create SMTP Transport
				$transport = Swift_SmtpTransport::newInstance($config['options']['hostname'], $port);

				if ( ! empty($config['options']['encryption']))
				{
					// Set encryption
					$transport->setEncryption($config['options']['encryption']);
				}
				
				// Do authentication, if part of the DSN
				empty($config['options']['username']) or $transport->setUsername($config['options']['username']);
				empty($config['options']['password']) or $transport->setPassword($config['options']['password']);

				// Set the timeout to 5 seconds
				$transport->setTimeout(empty($config['options']['timeout']) ? 5 : (int) $config['options']['timeout']);
			break;
			case 'sendmail':
				// Create a sendmail connection
				$transport = Swift_SendmailTransport::newInstance(empty($config['options']) ? "/usr/sbin/sendmail -bs" : $config['options']);

			break;
			default:
				// Use the native connection
				$transport = Swift_MailTransport::newInstance($config['options']);
			break;
		}

        // Getting Sender
        static::$_sender = static::config()->get('sender');

		// Create the SwiftMailer instance
		return static::$mail = Swift_Mailer::newInstance($transport);
	}

    public static function config()
    {
        return static::$_config ?: static::$_config = Kohana::$config->load('email');
    }

    /**
     * Send an email message.
     *
     * @param string|array $from sender email (and name)
     * @param string|array $to recipient email (and name), or an array of To, Cc, Bcc names
     * @param string $subject message subject
     * @param string $body message body
     * @param bool $html send email as HTML
     * @param bool $attach attach filename
     * @return int                      number of emails sent
     */
    public static function send($from, $to, $subject, $body, $html = FALSE, $attach = FALSE)
	{
		// Connect to SwiftMailer
		(static::$mail === NULL) and email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$msg = Swift_Message::newInstance($subject, $body, $html, 'utf-8');

		if (is_string($to))
		{
			// Single recipient
			$msg->setTo($to);
		}
		elseif (is_array($to))
		{
			if (isset($to[0]) AND isset($to[1]))
			{
				// Create To: address set
				$to = array('to' => $to);
			}

			foreach ($to as $method => $set)
			{
				if ( ! in_array($method, array('to', 'cc', 'bcc')))
				{
					// Use To: by default
					$method = 'to';
				}

				// Create method name
				$method = 'add'.ucfirst($method);

				if (is_array($set))
				{
					// Add a recipient with name
					$msg->$method($set[0], $set[1]);
				}
				else
				{
					// Add a recipient without name
					$msg->$method($set);
				}
			}
		}

        $sender = static::$_sender;

        // Use Sender email if no From email specified
        if ( !$from )
            $from = $sender;

        // From
        if (is_string($from))
        {
            // From without a name
            $msg->setFrom($from);

            // Set Reply-To header email only
            $msg->setReplyTo($from);
        }
        elseif (is_array($from))
        {
            // From with a name
            $msg->setFrom($from[0], $from[1]);

            // Set Reply-To header email with name
            $msg->setReplyTo($from[0], $from[1]);
        }

        // Sender
        if (is_string($sender))
        {
            // From without a name
            $msg->setSender($sender);
        }
        elseif (is_array($sender))
        {
            // Set Reply-To header email with name
            $msg->setSender($sender[0], $sender[1]);
        }

        // Attachments
        if( $attach )
        {
            $msg->attach(Swift_Attachment::fromPath($attach));
        }

		return static::$mail->send($msg);
	}

} // End email
