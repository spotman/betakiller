<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

final class SendScheduled extends AbstractTask
{
    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * SendScheduled constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $notification
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(NotificationFacade $notification, LoggerInterface $logger)
    {
        parent::__construct();

        $this->notification = $notification;
        $this->logger       = $logger;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $freq = $this->detectDueFrequency();

        if (!$freq) {
            return;
        }

        // For every scheduled group
        foreach ($this->notification->getScheduledGroups() as $group) {
            $this->logger->debug('Processing scheduled notification group ":name"', [
                ':name' => $group->getCodename(),
            ]);

            // Get all users with selected schedule option for group
            $targets = $this->notification->findGroupFreqTargets($group, $freq);

            // Skip group if no targets had selected freq
            if (!$targets) {
                continue;
            }

            $this->logger->debug(':count targets found for scheduled notification group ":name"', [
                ':count' => count($targets),
                ':name' => $group->getCodename(),
            ]);

            // Proceed "targets <=> messages" matrix
            foreach ($this->notification->getGroupMessagesCodenames($group) as $message) {
                foreach ($targets as $target) {
                    $this->processMessage($message, $target);
                }
            }
        }
    }

    private function detectDueFrequency(): ?NotificationFrequencyInterface
    {
        // Get all schedule options
        foreach ($this->notification->getScheduledFrequencies() as $freq) {
            // Check schedule option is due (check its crontab)
            if (!$freq->isDue()) { // TODO Inverse
                return $freq;
            }
        }

        return null;
    }

    private function processMessage(string $codename, MessageTargetInterface $target): void
    {
        // Check user-group spec and send notification

        $this->logger->info('Sending notification message ":name" to target ":who"', [
            ':name' => $codename,
            ':who'  => $target->getEmail(),
        ]);
    }
}
