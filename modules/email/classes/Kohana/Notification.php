<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Notification\NotificationException;

abstract class Kohana_Notification {

    use \BetaKiller\Utils\Instance\Simple;

    /**
     * @return Notification_Message
     */
    public function message()
    {
        return Notification_Message::instance();
    }

    public function send(Notification_Message $message)
    {
        $total = 0;

        $to = $message->get_to();

        if ( ! $to )
            throw new Exception('Message target must be specified');

        $transports = $this->get_transports();

        foreach ( $to as $target ) {
            $counter = 0;

            foreach ($transports as $transport) {
                try {
                    $counter = $transport->send($message, $target);

                    // Message delivered, exiting
                    if ($counter) {
                        break;
                    }
                } catch (Exception $e) {
                    Log::exception($e);
                    continue;
                }
            }

            // Message delivery failed
            if (!$counter) {
                throw new NotificationException('Message delivery failed, see previously logged exceptions');
            }

            $total += $counter;
        }

        return $total;
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Notification\TransportInterface
     */
    protected function transport_factory($name)
    {
        $class_name = '\\BetaKiller\\Notification\\Transport\\'.ucfirst($name).'Transport';

        return new $class_name;
    }

    /**
     * @return \BetaKiller\Notification\TransportInterface[]
     */
    protected function get_transports()
    {
        return [
            $this->transport_factory('online'),
            $this->transport_factory('email'),
        ];
    }

}
