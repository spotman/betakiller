<?php

namespace BetaKiller\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Exception;
use BetaKiller\Exception\DomainException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Model\NotificationFrequency;
use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfig;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\NotificationLog;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\Message\BroadcastMessageInterface;
use BetaKiller\Notification\Message\MessageInterface;
use BetaKiller\Notification\Transport\DismissibleTransportInterface;
use BetaKiller\Repository\NotificationFrequencyRepositoryInterface;
use BetaKiller\Repository\NotificationGroupRepositoryInterface;
use BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use Interop\Queue\Context;
use Interop\Queue\Producer;
use Interop\Queue\Queue;
use Psr\Log\LoggerInterface;
use Spotman\Acl\AclInterface;
use Throwable;

final readonly class NotificationFacade
{
    public const QUEUE_NAME_REGULAR  = 'notifications';
    public const QUEUE_NAME_PRIORITY = 'notifications.priority';

    /**
     * @var \Interop\Queue\Producer
     */
    private Producer $queueProducer;

    /**
     * @var \Interop\Queue\Queue
     */
    private Queue $priorityQueue;

    /**
     * @var \Interop\Queue\Queue
     */
    private Queue $regularQueue;

    /**
     * NotificationFacade constructor.
     *
     * @param \BetaKiller\Notification\TransportFactory                             $transportFactory
     * @param \BetaKiller\Notification\MessageFactory                               $messageFactory
     * @param \BetaKiller\Notification\MessageRendererInterface                     $renderer
     * @param \BetaKiller\Config\NotificationConfigInterface                        $config
     * @param \BetaKiller\Repository\NotificationGroupRepositoryInterface           $groupRepo
     * @param \BetaKiller\Repository\NotificationLogRepositoryInterface             $logRepo
     * @param \BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface $userConfigRepo
     * @param \BetaKiller\Repository\NotificationFrequencyRepositoryInterface       $freqRepo
     * @param \BetaKiller\Repository\UserRepositoryInterface                        $userRepo
     * @param \BetaKiller\Repository\RoleRepositoryInterface                        $roleRepo
     * @param \Interop\Queue\Context                                                $queueContext
     * @param \BetaKiller\Notification\MessageSerializer                            $serializer
     * @param \BetaKiller\Notification\MessageActionUrlGeneratorInterface           $actionUrlGenerator
     * @param \Spotman\Acl\AclInterface                                             $acl
     * @param \Psr\Log\LoggerInterface                                              $logger
     *
     * @throws \Interop\Queue\Exception\TimeToLiveNotSupportedException
     */
    public function __construct(
        private TransportFactory $transportFactory,
        private MessageFactory $messageFactory,
        private MessageRendererInterface $renderer,
        private NotificationConfigInterface $config,
        private NotificationGroupRepositoryInterface $groupRepo,
        private NotificationLogRepositoryInterface $logRepo,
        private NotificationGroupUserConfigRepositoryInterface $userConfigRepo,
        private NotificationFrequencyRepositoryInterface $freqRepo,
        private UserRepositoryInterface $userRepo,
        private RoleRepositoryInterface $roleRepo,
        private Context $queueContext,
        private MessageSerializer $serializer,
        private MessageActionUrlGeneratorInterface $actionUrlGenerator,
        private AclInterface $acl,
        private LoggerInterface $logger
    ) {
        $this->queueProducer = $this->queueContext->createProducer()->setTimeToLive(0); // Never expire
        $this->regularQueue  = $this->queueContext->createQueue(self::QUEUE_NAME_REGULAR);
        $this->priorityQueue = $this->queueContext->createQueue(self::QUEUE_NAME_PRIORITY);
    }

    /**
     * Create raw message
     *
     * @param string     $messageCodename
     * @param array|null $data
     * @param array|null $attachments Array of files to attach
     *
     * @return \BetaKiller\Notification\Message\MessageInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function createMessage(
        string $messageCodename,
        array $data = null,
        array $attachments = null
    ): MessageInterface {
        return $this->messageFactory->create($messageCodename, $data, $attachments);
    }

    /**
     * Enqueue immediate message for future processing (highest priority)
     *
     * @param \BetaKiller\Notification\EnvelopeInterface $envelope
     *
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function enqueueImmediate(EnvelopeInterface $envelope): void
    {
        if ($this->isMessageScheduled($envelope->getMessage())) {
            throw new NotificationException('Message ":name" is scheduled and must be sent via dedicated processor');
        }

        $this->enqueue($envelope);
    }

    /**
     * Enqueue scheduled message for future processing (lowest priority)
     *
     * @param \BetaKiller\Notification\EnvelopeInterface $envelope
     *
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function enqueueScheduled(EnvelopeInterface $envelope): void
    {
        if (!$this->isMessageScheduled($envelope->getMessage())) {
            throw new NotificationException('Message ":name" is not scheduled and must be send directly');
        }

        $this->enqueue($envelope);
    }

    public function testDelivery(string $messageCodename, MessageTargetInterface $target): void
    {
        $message = $this->messageFactory->createFromTarget($messageCodename, $target);

        $this->enqueueImmediate(new Envelope($target, $message));
    }

    /**
     * Send message immediately
     *
     * @param \BetaKiller\Notification\EnvelopeInterface $envelope
     *
     * @return bool
     * @throws \BetaKiller\Notification\TransportException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function send(EnvelopeInterface $envelope): bool
    {
        $target = $envelope->getTarget();
        $message = $envelope->getMessage();

        $transport = $this->getMessageTransport($message);

        if (!$this->isTransportSupported($envelope, $transport)) {
            return false;
        }

        $log = NotificationLog::createFrom($message, $target, $transport);

        try {
            // Fill subject line if the transport needs it
            if ($transport->isSubjectRequired()) {
                $subj = $this->renderer->makeSubject($message, $target);
                $message->setSubject($subj);
                $log->setSubject($subj);
            }

            // Render message template
            $body = $this->renderer->makeBody($message, $target, $transport);

            // Save body first to allow retry
            $log->setBody($body);

            // Send message via transport
            if ($transport->send($message, $target, $body)) {
                $log->markAsSucceeded();
            }
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            // Store exception as result
            $log->markAsFailed(Exception::oneLiner($e));
        }

        $this->logRepo->save($log);

        return $log->isSucceeded();
    }

    private function isTransportSupported(EnvelopeInterface $envelope, TransportInterface $transport): bool
    {
        $message = $envelope->getMessage();

        if (!$transport->canHandle($envelope)) {
            throw new TransportException('Transport ":transport" can not handle message ":message"', [
                ':transport' => $transport::getName(),
                ':message'   => $message::getCodename(),
            ]);
        }

        $target = $envelope->getTarget();

        return $transport->isEnabledFor($target);
    }

    public function retry(NotificationLogInterface $logRecord): bool
    {
        if ($logRecord->isSucceeded()) {
            throw new NotificationException('Can not retry succeeded message; hash ":hash"', [
                ':hash' => $logRecord->getHash(),
            ]);
        }

        $target = $this->detectLogRecordTarget($logRecord);

        if (!$target) {
            throw new NotificationException('Can not retry message without target; hash ":hash"', [
                ':hash' => $logRecord->getHash(),
            ]);
        }

        $body = $logRecord->getBody();

        if (!$body) {
            throw new NotificationException('Can not retry message without a body; hash ":hash"', [
                ':hash' => $logRecord->getHash(),
            ]);
        }

        try {
            $message   = $this->messageFactory->create($logRecord->getMessageName());
            $transport = $this->getMessageTransport($message);

            if ($transport->isSubjectRequired()) {
                $subject = $logRecord->getSubject();

                if (!$subject) {
                    throw new NotificationException('Can not retry message without a subject line; hash ":hash"', [
                        ':hash' => $logRecord->getHash(),
                    ]);
                }

                $message->setSubject($subject);
            }

            // Keep action URL
            if ($logRecord->hasActionUrl()) {
                $message->setActionUrl($logRecord->getActionUrl());
            }

            if ($transport->send($message, $target, $body)) {
                $logRecord->markAsSucceeded();
            } else {
                $logRecord->markAsRejected();
            }
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            // Store exception as result
            $logRecord->markAsFailed(Exception::oneLiner($e));
        }

        $this->logRepo->save($logRecord);

        return $logRecord->isSucceeded();
    }

    public function dismissDirect(string $messageCodename, MessageTargetInterface $target): void
    {
        if ($this->isBroadcastMessage($messageCodename)) {
            throw new NotificationException('Message ":name" is broadcast, use dedicated method instead', [
                ':name' => $messageCodename,
            ]);
        }

        $transport = $this->getDismissibleMessageTransport($messageCodename);

        $transport->dismissFor($messageCodename, $target);
    }

    public function dismissBroadcast(string $messageCodename): void
    {
        if (!$this->isBroadcastMessage($messageCodename)) {
            throw new NotificationException('Broadcast for message ":name" is not allowed', [
                ':name' => $messageCodename,
            ]);
        }

        $transport = $this->getDismissibleMessageTransport($messageCodename);
        $group     = $this->getGroupByMessageCodename($messageCodename);

        foreach ($this->getGroupTargets($group) as $target) {
            $transport->dismissFor($messageCodename, $target);
        }
    }

    public function isBroadcastMessage(string $messageCodename): bool
    {
        $fqcn = $this->config->getMessageClassName($messageCodename);

        return is_a($fqcn, BroadcastMessageInterface::class, true);
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
     * @return string[]
     */
    public function getGroupMessagesCodenames(NotificationGroupInterface $group): array
    {
        return $this->config->getGroupMessages($group->getCodename());
    }

    /**
     * Returns key-value pairs "messageCodename" => ["dismissOnEventName1", "dismissOnEventName2"]
     *
     * @return string[][]
     */
    public function getDismissibleMessages(): array
    {
        $data = [];

        foreach ($this->config->getGroups() as $groupCodename) {
            foreach ($this->config->getGroupMessages($groupCodename) as $messageCodename) {
                $dismissOn = $this->config->getMessageDismissOnEvents($messageCodename);

                if ($dismissOn) {
                    $data[$messageCodename] = $dismissOn;
                }
            }
        }

        return $data;
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface          $group
     * @param \BetaKiller\Model\NotificationFrequencyInterface|null $freq
     *
     * @return \BetaKiller\Notification\MessageTargetInterface[]
     */
    public function getGroupTargets(
        NotificationGroupInterface $group,
        NotificationFrequencyInterface $freq = null
    ): array {
        if ($freq && !$group->isFrequencyControlEnabled()) {
            throw new NotificationException('Frequency control is not allowed for group ":name"', [
                ':name' => $group->getCodename(),
            ]);
        }

        // Fetch target roles (including all in hierarchy)
        $roles = $group->getRoles();

        $users = [];

        // Get Users with provided roles (pre-fetch)
        foreach ($this->userRepo->getUsersWithRoles($roles) as $user) {
            // Exclude blocked users and users without confirmed email
            if (!$user->isActive()) {
                continue;
            }

            // Check user disabled this group
            if (!$group->isEnabledForUser($user)) {
                continue;
            }

            // Check freq is equal
            if ($freq) {
                $userFreq = $this->getGroupFrequency($group, $user);

                if ($userFreq->getCodename() !== $freq->getCodename()) {
                    continue;
                }
            }

            $users[] = $user;
        }

        return $users;
    }

    public function disableMessageForUser(string $messageCodename, UserInterface $user): void
    {
        $group = $this->getGroupByMessageCodename($messageCodename);

        $group->disableForUser($user);

        $this->groupRepo->save($group);
    }

    /**
     * Returns true if group is allowed for provided user (complex check with roles intersection)
     *
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     * @param \BetaKiller\Model\UserInterface              $user
     *
     * @return bool
     */
    public function isGroupAllowedToUser(NotificationGroupInterface $group, UserInterface $user): bool
    {
        // User has any of group roles => allowed
        return $this->acl->hasAnyAssignedRole($user, $group->getRoles());
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

    public function getGroupFrequency(
        NotificationGroupInterface $group,
        UserInterface $user
    ): NotificationFrequencyInterface {
        if (!$group->isFrequencyControlEnabled()) {
            throw new NotificationException('Frequency control of group ":name" is not allowed', [
                ':name' => $group->getCodename(),
            ]);
        }

        $config = $this->getGroupUserConfig($group, $user);

        return $config->hasFrequencyDefined()
            ? $config->getFrequency()
            : $this->getFrequencyByCodename(NotificationFrequency::FREQ_IMMEDIATELY);
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

    /**
     * @return NotificationGroupInterface[]
     */
    public function getScheduledGroups(): array
    {
        return $this->groupRepo->getScheduledGroups();
    }

    private function saveGroupUserConfig(NotificationGroupUserConfigInterface $config): void
    {
        $this->userConfigRepo->save($config);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return NotificationGroupInterface[]
     */
    public function getUserGroups(UserInterface $user): array
    {
        $roles = $this->roleRepo->getAllUserRoles($user);

        return $this->groupRepo->getRolesGroups($roles);
    }

    public function purgePriorityQueue(): void
    {
        $this->queueContext->purgeQueue($this->priorityQueue);
    }

    public function purgeRegularQueue(): void
    {
        $this->queueContext->purgeQueue($this->regularQueue);
    }

    private function isMessageEnabledForTarget(EnvelopeInterface $envelope): bool {
        $target = $envelope->getTarget();

        if (!$target instanceof UserInterface) {
            // Custom target types can not be checked here and always allowed
            return true;
        }

        $message = $envelope->getMessage();

        // Fetch group by message codename
        $group = $this->getMessageGroup($message);

        if (!$group->isEnabledForUser($target)) {
            return false;
        }

        $transport = $this->getMessageTransport($message);

        if (!$transport->isEnabledFor($target)) {
            return false;
        }

        if (!$this->isGroupAllowedToUser($group, $target)) {
            throw new DomainException('User ":user" is not allowed for notification group ":group"', [
                ':user'  => $target->getID(),
                ':group' => $group->getCodename(),
            ]);
        }

        return true;
    }

    /**
     * @param \BetaKiller\Notification\Message\MessageInterface $message
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function getMessageGroup(MessageInterface $message): NotificationGroupInterface
    {
        $messageCodename = $message::getCodename();

        return $this->getGroupByMessageCodename($messageCodename);
    }

    private function getMessageTransport(MessageInterface $message): TransportInterface
    {
        $name = $this->config->getMessageTransport($message::getCodename());

        return $this->transportFactory->create($name);
    }

    /**
     * Enqueue message for future processing
     *
     * @param \BetaKiller\Notification\EnvelopeInterface $envelope
     *
     * @throws \BetaKiller\Exception\DomainException
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    private function enqueue(EnvelopeInterface $envelope): void
    {
        // Send only if targets were specified or message group was allowed
        if (!$this->isMessageEnabledForTarget($envelope)) {
            return;
        }

        $message = $envelope->getMessage();

        // Preset right before serialization (all template data will be scalar after deserialization)
        $actionUrl = $this->actionUrlGenerator->make($message);

        if ($actionUrl) {
            $message->setActionUrl($actionUrl);
        }

        $body = $this->serializer->serialize($envelope);

        $queueMessage = $this->queueContext->createMessage($body);

        // Priority queue for critical messages
        $targetQueue = $envelope->getMessage()::isCritical()
            ? $this->priorityQueue
            : $this->regularQueue;

        // Enqueue
        $this->queueProducer->send($targetQueue, $queueMessage);
    }

    private function isMessageScheduled(MessageInterface $message): bool
    {
        return $this->getMessageGroup($message)->isFrequencyControlEnabled();
    }

    private function getDismissibleMessageTransport(string $messageCodename): DismissibleTransportInterface
    {
        $transportCodename = $this->config->getMessageTransport($messageCodename);

        $transport = $this->transportFactory->create($transportCodename);

        if (!$transport instanceof DismissibleTransportInterface) {
            throw new TransportException('Transport ":name" is not dismissible', [
                ':name' => $transportCodename,
            ]);
        }

        return $transport;
    }

    private function detectLogRecordTarget(NotificationLogInterface $log): ?MessageTargetInterface
    {
        $userId = $log->getTargetUserId();

        if ($userId) {
            return $this->userRepo->getById($userId);
        }

        $targetString = $log->getTargetIdentity();

        if (str_ends_with($targetString, '>')) {
            [$name, $email] = explode('<', trim($targetString, '>'));

            return new EmailMessageTarget($email, $name, $log->getLanguageIsoCode());
        }

        if (str_starts_with($targetString, '+')) {
            return new PhoneMessageTarget($targetString, $log->getLanguageIsoCode());
        }

        return null;
    }
}
