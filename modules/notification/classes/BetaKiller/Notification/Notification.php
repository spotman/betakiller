<?php
namespace BetaKiller\Notification;

use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Notification\Transport\OnlineTransport;

class Notification
{
    public static function instance()
    {
        return new static;
    }

    public function send(NotificationMessageInterface $message)
    {
        $total = 0;
        $to = $message->get_to();

        if (!$to) {
            throw new \Exception('Message target must be specified');
        }

        $transports = $this->get_transports();

        foreach ($to as $target) {
            $counter = 0;

            foreach ($transports as $transport) {
                if (!$transport->isEnabledFor($target)) {
                    continue;
                }

                try {
                    $counter = $transport->send($message, $target);

                    // Message delivered, exiting
                    if ($counter) {
                        break;
                    }
                } catch (\Exception $e) {
                    \Log::exception($e);
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
        /** @var OnlineTransport $online */
        $online = $this->transport_factory('online');

        /** @var EmailTransport $email */
        $email = $this->transport_factory('email');

        return [
            $online,
            $email,
        ];
    }
}
