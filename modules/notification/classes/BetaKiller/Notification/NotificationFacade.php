<?php
namespace BetaKiller\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Exception;
use BetaKiller\Exception\DomainException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\TextHelper;
use BetaKiller\Model\NotificationFrequency;
use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfig;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\NotificationLog;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\Transport\DismissibleTransportInterface;
use BetaKiller\Repository\NotificationFrequencyRepositoryInterface;
use BetaKiller\Repository\NotificationGroupRepositoryInterface;
use BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use DateTimeImmutable;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;
use Spotman\Acl\AclInterface;
use Throwable;

final class NotificationFacade
{
    public const QUEUE_NAME_REGULAR  = 'notifications';
    public const QUEUE_NAME_PRIORITY = 'notifications.priority';

    /**
     * @var \BetaKiller\Notification\MessageFactory
     */
    private $messageFactory;

    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepositoryInterface
     */
    private $groupRepo;

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
     * @var \Interop\Queue\Producer
     */
    private $queueProducer;

    /**
     * @var \Interop\Queue\Queue
     */
    private $priorityQueue;

    /**
     * @var \Interop\Queue\Queue
     */
    private $regularQueue;

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
     * @var \BetaKiller\Notification\TransportFactory
     */
    private $transportFactory;

    /**
     * @var \BetaKiller\Notification\MessageActionUrlGeneratorInterface
     */
    private $actionUrlGenerator;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
     */
    private RoleRepositoryInterface $roleRepo;

    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

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
        TransportFactory                               $transportFactory,
        MessageFactory                                 $messageFactory,
        MessageRendererInterface                       $renderer,
        NotificationConfigInterface                    $config,
        NotificationGroupRepositoryInterface           $groupRepo,
        NotificationLogRepositoryInterface             $logRepo,
        NotificationGroupUserConfigRepositoryInterface $userConfigRepo,
        NotificationFrequencyRepositoryInterface       $freqRepo,
        UserRepositoryInterface                        $userRepo,
        RoleRepositoryInterface                        $roleRepo,
        Context                                        $queueContext,
        MessageSerializer                              $serializer,
        MessageActionUrlGeneratorInterface             $actionUrlGenerator,
        AclInterface                                   $acl,
        LoggerInterface                                $logger
    ) {
        $this->transportFactory   = $transportFactory;
        $this->messageFactory     = $messageFactory;
        $this->userConfigRepo     = $userConfigRepo;
        $this->queueContext       = $queueContext;
        $this->serializer         = $serializer;
        $this->actionUrlGenerator = $actionUrlGenerator;
        $this->config             = $config;
        $this->groupRepo          = $groupRepo;
        $this->userRepo           = $userRepo;
        $this->roleRepo           = $roleRepo;
        $this->renderer           = $renderer;
        $this->freqRepo           = $freqRepo;
        $this->logRepo            = $logRepo;
        $this->acl                = $acl;
        $this->logger             = $logger;

        $this->queueProducer = $this->queueContext->createProducer()->setTimeToLive(0); // Never expire
        $this->regularQueue  = $this->queueContext->createQueue(self::QUEUE_NAME_REGULAR);
        $this->priorityQueue = $this->queueContext->createQueue(self::QUEUE_NAME_PRIORITY);
    }

    /**
     * Create raw message
     *
     * @param string                                          $messageCodename
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param array                                           $data
     * @param array|null                                      $attachments Array of files to attach
     *
     * @return \BetaKiller\Notification\MessageInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function createMessage(
        string                 $messageCodename,
        MessageTargetInterface $target,
        array                  $data,
        array                  $attachments = null
    ): MessageInterface {
        $transportName = $this->config->getMessageTransport($messageCodename);
        $isCritical    = $this->config->isMessageCritical($messageCodename);

        $message = $this->messageFactory->create($messageCodename, $target, $transportName, $isCritical)
            ->setTemplateData($data);

        if ($attachments) {
            foreach ($attachments as $attach) {
                $message->addAttachment($attach);
            }
        }

        $actionUrl = $this->actionUrlGenerator->make($message);

        if ($actionUrl) {
            $message->setActionUrl($actionUrl);
        }

        return $message;
    }

    /**
     * Enqueue immediate message for future processing (highest priority)
     *
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function enqueueImmediate(MessageInterface $message): void
    {
        if ($this->isMessageScheduled($message)) {
            throw new NotificationException('Message ":name" is scheduled and must be sent via dedicated processor');
        }

        $this->enqueue($message);
    }

    /**
     * Enqueue scheduled message for future processing (lowest priority)
     *
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function enqueueScheduled(MessageInterface $message): void
    {
        if (!$this->isMessageScheduled($message)) {
            throw new NotificationException('Message ":name" is not scheduled and must be send directly');
        }

        $this->enqueue($message);
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
        $transport = $this->transportFactory->create($message->getTransportName());

        if (!$this->isTransportSupported($message, $transport)) {
            return false;
        }

        $log = $this->createLogRecord($message, $transport);

        $target = $message->getTarget();

        try {
            // Fill subject line if transport need it
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

    private function isTransportSupported(MessageInterface $message, TransportInterface $transport): bool
    {
        if (!$transport->canHandle($message)) {
            throw new TransportException('Transport ":transport" can not handle message ":message"', [
                ':transport' => $transport::getName(),
                ':message'   => $message->getCodename(),
            ]);
        }

        $target = $message->getTarget();

        return $transport->isEnabledFor($target);
    }

    private function createLogRecord(MessageInterface $message, TransportInterface $transport): NotificationLogInterface
    {
        $target = $message->getTarget();

        return (new NotificationLog)
            ->setHash($message->getHash())
            ->setProcessedAt(new DateTimeImmutable)
            ->setMessageName($message->getCodename())
            ->setTarget($target)
            ->setTransport($transport)
            ->setLanguageIsoCode($target->getLanguageIsoCode());
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
            throw new NotificationException('Can not retry message without target User ID; hash ":hash"', [
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
            $transportName = $logRecord->getTransportName();

            $message   = $this->messageFactory->create($logRecord->getMessageName(), $target, $transportName, true);
            $transport = $this->transportFactory->create($transportName);

            if ($transport->isSubjectRequired()) {
                $subject = $logRecord->getSubject();

                if (!$subject) {
                    throw new NotificationException('Can not retry message without a subject line; hash ":hash"', [
                        ':hash' => $logRecord->getHash(),
                    ]);
                }

                $message->setSubject($subject);
            }

            if ($transport->send($message, $target, $body)) {
                $logRecord->markAsSucceeded();
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
        return $this->config->isMessageBroadcast($messageCodename);
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
        NotificationGroupInterface     $group,
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
            if ($user->isBlocked() || !$user->isEmailConfirmed()) {
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
        UserInterface              $user
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
        UserInterface              $user
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
        NotificationGroupInterface     $group,
        UserInterface                  $user,
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

    private function isMessageEnabledForTarget(
        MessageInterface $message
    ): bool {
        $target = $message->getTarget();

        if (!$target instanceof UserInterface) {
            // Custom target types can not be checked here and always allowed
            return true;
        }

        // Fetch group by message codename
        $group = $this->getMessageGroup($message);

        if (!$group->isEnabledForUser($target)) {
            return false;
        }

        $transport = $this->transportFactory->create($message->getTransportName());

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
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function getMessageGroup(MessageInterface $message): NotificationGroupInterface
    {
        $messageCodename = $message->getCodename();

        return $this->getGroupByMessageCodename($messageCodename);
    }

    /**
     * Enqueue message for future processing
     *
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function enqueue(MessageInterface $message): void
    {
        // Send only if targets were specified or message group was allowed
        if (!$this->isMessageEnabledForTarget($message)) {
            return;
        }

        $body = $this->serializer->serialize($message);

        $queueMessage = $this->queueContext->createMessage($body);

        // Priority queue for critical messages
        $targetQueue = $message->isCritical()
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

        $targetString = $log->getTargetString();

        if (!TextHelper::contains($targetString, '<')) {
            return null;
        }

        [$name, $email] = explode('<', trim($targetString, '>'));

        return new MessageTargetEmail($email, $name, $log->getLanguageIsoCode());
    }
}
