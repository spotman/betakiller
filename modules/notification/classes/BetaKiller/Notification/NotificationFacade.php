<?php
namespace BetaKiller\Notification;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Notification\Transport\OnlineTransport;
use Psr\Log\LoggerInterface;

class NotificationFacade
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Notification\NotificationMessageFactory
     */
    private $messageFactory;

    /**
     * @var \BetaKiller\Notification\NotificationTransportInterface[]
     */
    private $transports;

    /**
     * @var \BetaKiller\Notification\MessageRendererInterface
     */
    private $renderer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * NotificationFacade constructor.
     *
     * @param \BetaKiller\Notification\NotificationMessageFactory $messageFactory
     * @param \Psr\Log\LoggerInterface                            $logger
     * @param \BetaKiller\Notification\MessageRendererInterface   $renderer
     */
    public function __construct(NotificationMessageFactory $messageFactory, LoggerInterface $logger, MessageRendererInterface $renderer)
    {
        $this->logger   = $logger;
        $this->renderer = $renderer;
        $this->messageFactory = $messageFactory;
    }

    public function create(string $name = null): NotificationMessageInterface
    {
        return $this->messageFactory->create($name);
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return int
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function send(NotificationMessageInterface $message): int
    {
        $total = 0;
        $to    = $message->getTargets();

        if (!$to) {
            throw new NotificationException('Message target must be specified');
        }

        $transports = $this->getTransports();

        foreach ($to as $target) {
            $counter  = 0;
            $attempts = 0;

            foreach ($transports as $transport) {
                if (!$transport->isEnabledFor($target)) {
                    continue;
                }

                $attempts++;

                try {
                    $counter = $transport->send($message, $target, $this->renderer);

                    // Message delivered, exiting
                    if ($counter) {
                        $this->logger->debug('Notification sent to user with email :email with data :data', [
                            ':email' => $target->getEmail(),
                            ':data'  => json_encode($message->getTemplateData()),
                        ]);
                        break;
                    }
                } catch (\Throwable $e) {
                    $this->logException($this->logger, $e);
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
     * @return \BetaKiller\Notification\NotificationTransportInterface
     */
    protected function transportFactory($name): NotificationTransportInterface
    {
        $className = '\\BetaKiller\\Notification\\Transport\\'.ucfirst($name).'Transport';

        return new $className;
    }

    /**
     * @return \BetaKiller\Notification\NotificationTransportInterface[]
     */
    protected function getTransports(): array
    {
        if (!$this->transports) {
            $this->transports = $this->createTransports();
        }

        return $this->transports;
    }

    /**
     * @return \BetaKiller\Notification\NotificationTransportInterface[]
     */
    private function createTransports(): array
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
