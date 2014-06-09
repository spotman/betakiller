<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_Notification {

    use Util_Singleton;

    const TRANSPORT_EMAIL = 'email';

    /**
     * @return Notification_Message
     */
    public function message()
    {
        return Notification_Message::factory();
    }

    public function send(Kohana_Notification_Message $message)
    {
        $from = $message->get_from();
        $to = $message->get_to();
        $subj = $message->get_subj();

//        if ( ! $from )
//            throw new Exception('Message source must be specified');

        if ( ! $to )
            throw new Exception('Message target must be specified');

        foreach ( $to as $target )
        {
            if ( $target->is_online() AND $target->is_online_notification_allowed() )
            {
                // Online notification
                throw new HTTP_Exception_501;
            }
            else if ( $target->is_email_notification_allowed() )
            {
                $body = $this->render_message($message, static::TRANSPORT_EMAIL);

                $from = $message->get_from();

                if ( $from )
                {
                    // TODO Добавление заголовков об отправке от имени отправляющего "через" дефолтный почтовый ящик
                }

                // Email notification
                Email::send(
                    NULL,
                    $target->get_email(),
                    $subj,
                    $body,
                    TRUE
                );
            }
        }
    }

    protected function render_message(Kohana_Notification_Message $message, $transport)
    {
        return $message->render($transport);
    }

}