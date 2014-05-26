<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_Notification {

    use Util_Singleton;

    public function message()
    {
        return new Notification_Message;
    }

    public function send(Kohana_Notification_Message $message)
    {
//        $from = $message->get_from();
        $to = $message->get_to();
        $subj = $message->get_subj();
        $text = $message->get_text();

//        if ( ! $from )
//            throw new Exception('Message source must be specified');

        if ( ! $to )
            throw new Exception('Message target must be specified');

        if ( ! $text )
            throw new Exception('Message text must be specified');

        foreach ( $to as $target )
        {
            if ( $target->is_online() AND $target->is_online_notification_allowed() )
            {
                // Online notification
                throw new HTTP_Exception_501;
            }
            else if ( $target->is_email_notification_allowed() )
            {
                // Email notification
                Email::send(
                    NULL, // $from->get_email(),
                    $target->get_email(),
                    $subj,
                    $text,
                    TRUE
                );
            }
        }
    }

}