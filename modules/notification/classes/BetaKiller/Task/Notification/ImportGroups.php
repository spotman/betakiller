<?php

declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Notification\DismissBroadcastOnEventMessageInterface;
use BetaKiller\Notification\DismissDirectOnEventMessageInterface;
use BetaKiller\Notification\NotificationException;
use BetaKiller\Notification\Transport\DismissibleTransportInterface;
use BetaKiller\Notification\TransportException;
use BetaKiller\Notification\TransportFactory;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ImportGroups extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
     */
    private $roleRepo;

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $groupRepo;

    /**
     * @var \BetaKiller\Notification\TransportFactory
     */
    private $transportFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepository $groupRepo
     * @param \BetaKiller\Config\NotificationConfigInterface     $config
     * @param \BetaKiller\Repository\RoleRepositoryInterface     $roleRepo
     * @param \BetaKiller\Notification\TransportFactory          $transportFactory
     * @param \Psr\Log\LoggerInterface                           $logger
     */
    public function __construct(
        NotificationGroupRepository $groupRepo,
        NotificationConfigInterface $config,
        RoleRepositoryInterface $roleRepo,
        TransportFactory $transportFactory,
        LoggerInterface $logger
    ) {
        $this->groupRepo        = $groupRepo;
        $this->config           = $config;
        $this->roleRepo         = $roleRepo;
        $this->transportFactory = $transportFactory;
        $this->logger           = $logger;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $groupCodenames = $this->config->getGroups();

        // Disable unused groups
        foreach ($this->groupRepo->getAllEnabled() as $group) {
            if (!\in_array($group->getCodename(), $groupCodenames, true)) {
                // Disable unused group
                $group->disable();
                $this->groupRepo->save($group);

                $this->logger->info('Group ":group" was disabled', [
                    ':group' => $group->getCodename(),
                ]);
            }
        }

        $place = 0;

        // Add new groups / re-enable existing
        foreach ($groupCodenames as $groupCodename) {
            $group = $this->groupRepo->findByCodename($groupCodename);

            if (!$group) {
                // Create new group
                $group = (new NotificationGroup())
                    ->setCodename($groupCodename)
                    ->enable();

                $this->groupRepo->save($group);

                $this->logger->info('Group ":group" created', [
                    ':group' => $groupCodename,
                ]);
            } elseif (!$group->isEnabled()) {
                // Re-enable existing group
                $group->enable();
                $this->groupRepo->save($group);

                $this->logger->info('Group ":group" was re-enabled', [
                    ':group' => $group->getCodename(),
                ]);
            }

            // Update order
            $group->setPlace($place++);

            $this->importGroup($group);

            $this->validateGroup($group);
        }
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function importGroup(NotificationGroupInterface $group): void
    {
        $groupCodename = $group->getCodename();

        if ($this->config->isSystemGroup($groupCodename)) {
            $group->markAsSystem();
        } else {
            $group->markAsRegular();
        }

        if ($this->config->isGroupFreqControlled($groupCodename)) {
            $group->enableFrequencyControl();
        } else {
            $group->disableFrequencyControl();
        }

        // Get updated roles
        $rolesCodenames = $this->config->getGroupRoles($groupCodename);

        // Remove unused roles
        foreach ($group->getRoles() as $currentRole) {
            if (!\in_array($currentRole->getName(), $rolesCodenames, true)) {
                // Unused roles => remove it
                $group->removeRole($currentRole);
                $this->logger->info('Role ":role" removed from group ":group"', [
                    ':role'  => $currentRole->getName(),
                    ':group' => $groupCodename,
                ]);
            }
        }

        // Add new roles
        foreach ($rolesCodenames as $roleCodename) {
            $role = $this->roleRepo->getByName($roleCodename);

            if (!$group->hasRole($role)) {
                $group->addRole($role);
                $this->logger->info('Role ":role" added to group ":group"', [
                    ':role'  => $roleCodename,
                    ':group' => $groupCodename,
                ]);
            }
        }

        $this->groupRepo->save($group);
    }

    private function validateGroup(NotificationGroupInterface $group): void
    {
        $messages = $this->config->getGroupMessages($group->getCodename());

        if (!$messages) {
            throw new NotificationException('Notification group ":name" has no messages', [
                ':name' => $group->getCodename(),
            ]);
        }

        $isScheduled = $group->isFrequencyControlEnabled();

        if ($isScheduled && count($messages) > 1) {
            throw new NotificationException('Multiple messages in scheduled group ":name" are not allowed', [
                ':name' => $group->getCodename(),
            ]);
        }

        foreach ($messages as $messageCodename) {
            $transportCodename = $this->config->getMessageTransport($messageCodename);
            $dismissOnEvents   = $this->config->getMessageDismissOnEvents($messageCodename);
            $isBroadcast       = $this->config->isMessageBroadcast($messageCodename);

            $transport = $this->transportFactory->create($transportCodename);

            if ($dismissOnEvents && !$transport instanceof DismissibleTransportInterface) {
                throw new TransportException(
                    'Message ":message" has dismiss_on events but transport ":transport" is not dismissible', [
                    ':message'   => $messageCodename,
                    ':transport' => $transportCodename,
                ]
                );
            }

            $targetEventType = $isBroadcast
                ? DismissBroadcastOnEventMessageInterface::class
                : DismissDirectOnEventMessageInterface::class;

            foreach ($dismissOnEvents as $dismissOn) {
                if (!is_a($dismissOn, $targetEventType, true)) {
                    throw new TransportException(
                        'Message ":message" dismiss_on event ":event" must implement :must', [
                        ':message' => $messageCodename,
                        ':event'   => $dismissOn,
                        ':must'    => $targetEventType,
                    ]
                    );
                }
            }
        }
    }
}
