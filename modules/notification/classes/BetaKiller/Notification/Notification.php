<?php
namespace BetaKiller\Notification;

use BetaKiller\Log\LoggerHelper;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Notification\Transport\OnlineTransport;

class Notification
{
    /**
     * @var \BetaKiller\Log\LoggerHelper
     */
    private $loggerHelper;

    /**
     * Notification constructor.
     *
     * @param \BetaKiller\Log\LoggerHelper $loggerHelper
     */
    public function __construct(LoggerHelper $loggerHelper)
    {
        $this->loggerHelper = $loggerHelper;
    }

    public function send(NotificationMessageInterface $message): int
    {
        $total = 0;
        $to = $message->getTargets();

        if (!$to) {
            throw new NotificationException('Message target must be specified');
        }

        $transports = $this->getTransports();

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
                        $this->loggerHelper->debug('Notification sent to user with email :email with data :data', [
                            ':email'    =>  $target->getEmail(),
                            ':data'     =>  json_encode($message->getTemplateData())
                        ]);
                        break;
                    }
                } catch (\Throwable $e) {
                    $this->loggerHelper->logException($e);
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
    protected function transportFactory($name): TransportInterface
    {
        $className = '\\BetaKiller\\Notification\\Transport\\'.ucfirst($name).'Transport';

        return new $className;
    }

    /**
     * @return \BetaKiller\Notification\TransportInterface[]
     */
    protected function getTransports(): array
    {
        /** @var OnlineTransport $online */
        $online = $this->transportFactory('online');

        /** @var EmailTransport $email */
        $email = $this->transportFactory('email');

        return [
            $online,
            $email,
        ];
    }
}
