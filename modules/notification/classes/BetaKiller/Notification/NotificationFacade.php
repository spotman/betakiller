<?php
namespace BetaKiller\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Notification\Transport\OnlineTransport;
use BetaKiller\Repository\NotificationGroupRepository;
use Psr\Log\LoggerInterface;

class NotificationFacade
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Notification\NotificationMessageFactory
     */
    private $messageFactory;

    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $groupRepo;

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
     * @param \BetaKiller\Notification\MessageRendererInterface   $renderer
     * @param \BetaKiller\Config\NotificationConfigInterface      $config
     * @param \BetaKiller\Repository\NotificationGroupRepository  $groupRepo
     * @param \Psr\Log\LoggerInterface                            $logger
     */
    public function __construct(
        NotificationMessageFactory $messageFactory,
        MessageRendererInterface $renderer,
        NotificationConfigInterface $config,
        NotificationGroupRepository $groupRepo,
        LoggerInterface $logger
    ) {
        $this->messageFactory = $messageFactory;
        $this->config         = $config;
        $this->groupRepo      = $groupRepo;
        $this->renderer       = $renderer;
        $this->logger         = $logger;
    }

    /**
     * Create message and add group users
     *
     * @param string $name
     * @param array  $templateData
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function groupMessage(string $name, array $templateData): NotificationMessageInterface
    {
        $message = $this->createMessage($name, $templateData);

        // Add targets from group
        $this->addGroupTargets($message);

        return $message;
    }

    /**
     * Create direct message
     *
     * @param string                                             $name
     * @param \BetaKiller\Notification\NotificationUserInterface $target
     * @param array                                              $templateData
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function directMessage(
        string $name,
        NotificationUserInterface $target,
        array $templateData
    ): NotificationMessageInterface {
        $message = $this->createMessage($name, $templateData);

        $message->addTarget($target);

        return $message;
    }

    /**
     * Create raw message
     *
     * @param string $name
     * @param array  $templateData
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function createMessage(string $name, array $templateData): NotificationMessageInterface
    {
        $message = $this->messageFactory->create($name);

        $message->setTemplateData($templateData);

        return $message;
    }

    /**
     * Send message
     *
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return int
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function send(NotificationMessageInterface $message): int
    {
        $to = $message->getTargets();

        if (!$to) {
            throw new NotificationException('Message target must be specified');
        }

        $transports = $this->getTransports();
        $total      = 0;

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

    private function addGroupTargets(NotificationMessageInterface $message): void
    {
        // Fetch group by message codename
        $group = $this->getMessageGroup($message);

        // Fetch targets (users) by group
        $users = $this->groupRepo->findGroupUsers($group);

        if (!$users) {
            throw new NotificationException('No users found for group ":codename"', [
                    ':codename' => $group->getCodename(),
                ]
            );
        }

        // Add targets to message
        $message->addTargetUsers($users);
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getMessageGroup(NotificationMessageInterface $message): NotificationGroupInterface
    {
        $messageCodename = $message->getCodename();

        // Fetch group by message codename
        $groupCodename = $this->config->getMessageGroup($messageCodename);

        if (!$groupCodename) {
            throw new NotificationException(
                'Group not found for message codename ":codename"', [
                    ':codename' => $messageCodename,
                ]
            );
        }

        return $this->groupRepo->getByCodename($groupCodename);
    }
}
