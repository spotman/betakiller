<?php
namespace BetaKiller\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Exception\DomainException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationLog;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Notification\Transport\OnlineTransport;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\NotificationLogRepository;
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
     * @var \BetaKiller\Repository\NotificationLogRepository
     */
    private $logRepo;

    /**
     * NotificationFacade constructor.
     *
     * @param \BetaKiller\Notification\NotificationMessageFactory $messageFactory
     * @param \BetaKiller\Notification\MessageRendererInterface   $renderer
     * @param \BetaKiller\Config\NotificationConfigInterface      $config
     * @param \BetaKiller\Repository\NotificationGroupRepository  $groupRepo
     * @param \BetaKiller\Repository\NotificationLogRepository    $logRepo
     * @param \Psr\Log\LoggerInterface                            $logger
     */
    public function __construct(
        NotificationMessageFactory $messageFactory,
        MessageRendererInterface $renderer,
        NotificationConfigInterface $config,
        NotificationGroupRepository $groupRepo,
        NotificationLogRepository $logRepo,
        LoggerInterface $logger
    ) {
        $this->messageFactory = $messageFactory;
        $this->config         = $config;
        $this->groupRepo      = $groupRepo;
        $this->renderer       = $renderer;
        $this->logRepo        = $logRepo;
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
     * @param string                                               $name
     * @param \BetaKiller\Notification\NotificationTargetInterface $target
     * @param array                                                $templateData
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function directMessage(
        string $name,
        NotificationTargetInterface $target,
        array $templateData
    ): NotificationMessageInterface {
        $message = $this->createMessage($name, $templateData);

        if ($this->isMessageEnabledForUser($message, $target)) {
            $message->addTarget($target);
        }

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

                if ($this->sendMessage($message, $target, $transport)) {
                    $counter++;
                }

                $attempts++;
            }

            // Message delivery failed
            if ($attempts && !$counter) {
                throw new NotificationException('Message delivery failed, see previously logged exceptions');
            }

            $total += $counter;
        }

        return $total;
    }

    private function sendMessage(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target,
        NotificationTransportInterface $transport
    ): bool {
        $log = new NotificationLog;

        try {
            $log
                ->setProcessedAt(new \DateTimeImmutable)
                ->setMessageName($message->getCodename())
                ->setTarget($target)
                ->setTransport($transport);

            // Fill subject line if transport needed
            if ($transport->isSubjectRequired()) {
                $subj = $this->renderer->makeSubject($message, $target);
                $message->setSubject($subj);
                $log->setSubject($subj);
            }

            // Render message template
            $body = $this->renderer->makeBody($message, $target, $transport);

            // Save data to log file (transport name, target string (email, phone, etc), body)
            $log->setBody($body);

            // Send message via transport
            $counter = $transport->send($message, $target, $body);

            // Message delivered, exiting
            if ($counter) {
                $log->markAsSucceeded();
            }
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            // Store exception as result
            $log->markAsFailed($e->getMessage());
        }

        $this->logRepo->save($log);

        return $log->isSucceeded();
    }

    public function getGroupByMessageCodename(string $messageCodename): NotificationGroupInterface
    {
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

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @return \BetaKiller\Notification\NotificationTargetInterface[]
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getGroupTargets(NotificationGroupInterface $group): array
    {
        return $this->groupRepo->findGroupUsers($group);
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
    public function getTransports(): array
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
//        $online = $this->transportFactory('online');

        /** @var EmailTransport $email */
        $email = $this->transportFactory('email');

        return [
//            $online,
            $email,
        ];
    }

    private function isMessageEnabledForUser(
        NotificationMessageInterface $message,
        NotificationTargetInterface $user
    ): bool {
        if (!$user instanceof UserInterface) {
            // Custom target types can not be checked here and always allowed
            return true;
        }

        // Fetch group by message codename
        $group = $this->getMessageGroup($message);

        if (!$group->isEnabledForUser($user)) {
            return false;
        }

        if (!$group->isAllowedToUser($user)) {
            throw new DomainException('User ":user" is not allowed for notification group ":group"', [
                ':user'  => $user->getUsername(),
                ':group' => $group->getCodename(),
            ]);
        }

        return true;
    }

    private function addGroupTargets(NotificationMessageInterface $message): void
    {
        // Fetch group by message codename
        $group = $this->getMessageGroup($message);

        // Fetch targets (users) by group
        $users = $this->getGroupTargets($group);

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
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getMessageGroup(NotificationMessageInterface $message): NotificationGroupInterface
    {
        $messageCodename = $message->getCodename();

        return $this->getGroupByMessageCodename($messageCodename);
    }
}
