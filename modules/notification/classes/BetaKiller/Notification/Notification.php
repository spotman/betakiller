<?php
namespace BetaKiller\Notification;

use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Notification\Transport\OnlineTransport;
use BetaKiller\Helper\LogTrait;

class Notification
{
    use LogTrait;

    public static function instance()
    {
        return new static;
    }

    public function send(NotificationMessageInterface $message)
    {
        $total = 0;
        $to = $message->get_to();

        if (!$to) {
            throw new NotificationException('Message target must be specified');
        }

        $transports = $this->get_transports();

        foreach ($to as $target) {
            $counter = 0;
            $attempts = 0;

            foreach ($transports as $transport) {
                if (!$transport->isEnabledFor($target)) {
                    continue;
                }

                $attempts++;

                try {
                    $counter = $transport->send($message, $target);

                    // Message delivered, exiting
                    if ($counter) {
                        $this->debug('Notification sent to user with email :email with data :data', [
                            ':email'    =>  $target->get_email(),
                            ':data'     =>  json_encode($message->get_template_data())
                        ]);
                        break;
                    }
                } catch (\Exception $e) {
                    $this->exception($e);
                    continue;
                }
            }

            // Message delivery failed
            if ($attempts && !$counter) {
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
