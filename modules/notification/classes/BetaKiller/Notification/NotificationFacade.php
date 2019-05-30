<?php
namespace BetaKiller\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Exception\DomainException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfig;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\NotificationLog;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Notification\Transport\OnlineTransport;
use BetaKiller\Repository\NotificationFrequencyRepositoryInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use DateTimeImmutable;
use Enqueue\Dbal\DbalMessage;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;
use Throwable;

class NotificationFacade
{
    use LoggerHelperTrait;

    public const QUEUE_NAME = 'notifications';

    /**
     * @var \BetaKiller\Notification\MessageFactory
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
     * @var \BetaKiller\Notification\TransportInterface[]
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
     * @var \BetaKiller\Repository\NotificationLogRepositoryInterface
     */
    private $logRepo;

    /**
     * @var \Interop\Queue\Context
     */
    private $queueContext;

    /**
     * @var \BetaKiller\Notification\MessageSerializer
     */
    private $serializer;

    /**
     * @var \BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface
     */
    private $userConfigRepo;

    /**
     * @var \BetaKiller\Repository\NotificationFrequencyRepositoryInterface
     */
    private $freqRepo;

    /**
     * NotificationFacade constructor.
     *
     * @param \BetaKiller\Notification\MessageFactory                               $messageFactory
     * @param \BetaKiller\Notification\MessageRendererInterface                     $renderer
     * @param \BetaKiller\Config\NotificationConfigInterface                        $config
     * @param \BetaKiller\Repository\NotificationGroupRepository                    $groupRepo
     * @param \BetaKiller\Repository\NotificationLogRepositoryInterface             $logRepo
     * @param \BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface $userConfigRepo
     * @param \BetaKiller\Repository\NotificationFrequencyRepositoryInterface       $freqRepo
     * @param \Interop\Queue\Context                                                $queueContext
     * @param \BetaKiller\Notification\MessageSerializer                            $serializer
     * @param \Psr\Log\LoggerInterface                                              $logger
     */
    public function __construct(
        MessageFactory $messageFactory,
        MessageRendererInterface $renderer,
        NotificationConfigInterface $config,
        NotificationGroupRepository $groupRepo,
        NotificationLogRepositoryInterface $logRepo,
        NotificationGroupUserConfigRepositoryInterface $userConfigRepo,
        NotificationFrequencyRepositoryInterface $freqRepo,
        Context $queueContext,
        MessageSerializer $serializer,
        LoggerInterface $logger
    ) {
        $this->messageFactory = $messageFactory;
        $this->userConfigRepo = $userConfigRepo;
        $this->queueContext   = $queueContext;
        $this->serializer     = $serializer;
        $this->config         = $config;
        $this->groupRepo      = $groupRepo;
        $this->renderer       = $renderer;
        $this->freqRepo       = $freqRepo;
        $this->logRepo        = $logRepo;
        $this->logger         = $logger;
    }

    /**
     * Create raw message
     *
     * @param string                                   $name
     * @param \BetaKiller\Notification\TargetInterface $target
     * @param array                                    $data
     * @param array|null                               $attachments Array of files to attach
     *
     * @return \BetaKiller\Notification\MessageInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function createMessage(
        string $name,
        TargetInterface $target,
        array $data,
        array $attachments = null
    ): MessageInterface {
        $message = $this->messageFactory->create($name)
            ->setTemplateData($data)
            ->setTarget($target);

        if ($attachments) {
            foreach ($attachments as $attach) {
                $message->addAttachment($attach);
            }
        }

        return $message;
    }

    /**
     * Enqueue message for future processing
     *
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function enqueue(MessageInterface $message): void
    {
        $target = $message->getTarget();

        // Send only if targets were specified or message group was allowed
        if (!$this->isMessageEnabledForUser($message, $target)) {
            return;
        }

        $body = $this->serializer->serialize($message);

        $queue        = $this->queueContext->createQueue(self::QUEUE_NAME);
        $queueMessage = $this->queueContext->createMessage($body);

        if (!$queueMessage instanceof DbalMessage) {
            throw new NotificationException('Wrong queue message type ":class"', [
                ':class' => get_class($queueMessage),
            ]);
        }

        // Apply user settings
        if ($target instanceof UserInterface) {
            // Get linked group
            $group = $this->getMessageGroup($message);

            // Get user config
            $config = $this->getGroupUserConfig($group, $target);

            // Set delivery time
            if ($config->hasFrequencyDefined()) {
                $schedule = $config->getFrequency()->calculateSchedule();

                $delay = $schedule->getTimestamp() - time();

                $queueMessage->setDeliveryDelay($delay * 1000);
            }
        }

        // Enqueue
        $this->queueContext->createProducer()->send($queue, $queueMessage);
    }

    /**
     * Send message immediately
     *
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return bool
     */
    public function send(MessageInterface $message): bool
    {
        $target = $message->getTarget();

        $counter  = 0;
        $attempts = 0;

        foreach ($this->getTransports() as $transport) {
            if (!$transport->isEnabledFor($target)) {
                continue;
            }

            if ($this->sendThrough($message, $transport)) {
                $counter++;
            }

            $attempts++;
        }

        // Check message delivery failed
        return !($attempts && !$counter);
    }

    private function sendThrough(MessageInterface $message, TransportInterface $transport): bool
    {
        $target = $message->getTarget();

        $hash = $this->calculateHash($message, $target, $transport);

        $log = new NotificationLog;

        $log->setHash($hash);

        try {
            $log
                ->setProcessedAt(new DateTimeImmutable)
                ->setMessageName($message->getCodename())
                ->setTarget($target)
                ->setTransport($transport)
                ->setLanguageIsoCode($target->getLanguageIsoCode());

            // Fill subject line if transport need it
            if ($transport->isSubjectRequired()) {
                $subj = $this->renderer->makeSubject($message, $target);
                $message->setSubject($subj);
                $log->setSubject($subj);
            }

            // Render message template
            $body = $this->renderer->makeBody($message, $target, $transport, $hash);

            // Save data to log file (transport name, target string (email, phone, etc), body)
            $log->setBody($body);

            // Send message via transport
            if ($transport->send($message, $target, $body)) {
                $log->markAsSucceeded();
            }
        } catch (Throwable $e) {
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
            throw new NotificationException('Group not found for message codename ":codename"', [
                ':codename' => $messageCodename,
            ]);
        }

        return $this->groupRepo->getByCodename($groupCodename);
    }

    public function getGroupByCodename(string $groupCodename): NotificationGroupInterface
    {
        return $this->groupRepo->getByCodename($groupCodename);
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @return \BetaKiller\Notification\TargetInterface[]
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getGroupTargets(NotificationGroupInterface $group): array
    {
        $users = $this->groupRepo->findGroupUsers($group);

        if (!$users) {
            throw new NotificationException('No users found for group ":codename"', [
                ':codename' => $group->getCodename(),
            ]);
        }

        return $users;
    }

    public function disableMessageForUser(string $messageCodename, UserInterface $user): void
    {
        $group = $this->getGroupByMessageCodename($messageCodename);

        $group->disableForUser($user);

        $this->groupRepo->save($group);
    }

    public function getGroupUserConfig(
        NotificationGroupInterface $group,
        UserInterface $user
    ): NotificationGroupUserConfigInterface {
        $config = $this->userConfigRepo->findByUserAndGroup($group, $user);

        // Create new object if user has no config yet
        if (!$config) {
            $config = new NotificationGroupUserConfig();

            $config
                ->bindToUser($user)
                ->bindToGroup($group);
        }

        return $config;
    }

    public function setGroupFrequency(
        NotificationGroupInterface $group,
        UserInterface $user,
        NotificationFrequencyInterface $freq
    ): void {
        if (!$group->isFrequencyControlEnabled()) {
            throw new NotificationException('Frequency control of group ":name" is not allowed', [
                ':name' => $group->getCodename(),
            ]);
        }

        $config = $this->getGroupUserConfig($group, $user);

        $config->setFrequency($freq);

        $this->saveGroupUserConfig($config);
    }

    public function getFrequencyByCodename(string $codename): NotificationFrequencyInterface
    {
        return $this->freqRepo->getByCodename($codename);
    }

    private function saveGroupUserConfig(NotificationGroupUserConfigInterface $config): void
    {
        $this->userConfigRepo->save($config);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getUserGroups(UserInterface $user): array
    {
        return $this->groupRepo->getUserGroups($user);
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
    public function getTransports(): array
    {
        if (!$this->transports) {
            $this->transports = $this->createTransports();
        }

        return $this->transports;
    }

    /**
     * @return \BetaKiller\Notification\TransportInterface[]
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
        MessageInterface $message,
        TargetInterface $user
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
                ':user'  => $user->getID(),
                ':group' => $group->getCodename(),
            ]);
        }

        return true;
    }

    /**
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getMessageGroup(MessageInterface $message): NotificationGroupInterface
    {
        $messageCodename = $message->getCodename();

        return $this->getGroupByMessageCodename($messageCodename);
    }

    private function calculateHash(
        MessageInterface $message,
        TargetInterface $target,
        TransportInterface $transport
    ): string {
        return sha1(implode('-', [
            microtime(),
            $message->getCodename(),
            $target->getEmail(),
            $transport->getName(),
        ]));
    }
}
