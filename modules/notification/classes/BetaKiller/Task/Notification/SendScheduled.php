<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\ScheduleProcessor\ScheduleProcessorInterface;
use BetaKiller\Notification\ScheduleProcessorFactory;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;

final class SendScheduled extends AbstractTask
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Notification\ScheduleProcessorFactory
     */
    private $processorFactory;

    /**
     * SendScheduled constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade       $notification
     * @param \BetaKiller\Notification\ScheduleProcessorFactory $processorFactory
     * @param \Psr\Log\LoggerInterface                          $logger
     */
    public function __construct(
        NotificationFacade $notification,
        ScheduleProcessorFactory $processorFactory,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->notification     = $notification;
        $this->processorFactory = $processorFactory;
        $this->logger           = $logger;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [
            'freq' => null,
        ];
    }

    public function run(): void
    {
        $freqCodename = (string)$this->getOption('freq', true);

        $freq = $this->notification->getFrequencyByCodename($freqCodename);

        $this->logger->debug('Processing notification for ":freq" schedule', [
            ':freq' => $freq->getCodename(),
        ]);

        // For every scheduled group
        foreach ($this->notification->getScheduledGroups() as $group) {
            $this->logger->debug('Processing scheduled notification group ":name"', [
                ':name' => $group->getCodename(),
            ]);

            $messages = $this->notification->getGroupMessagesCodenames($group);

            if (count($messages) > 1) {
                $this->logException($this->logger, new TaskException(
                    'Multiple messages in scheduled group ":name" are not allowed', [
                    ':name' => $group->getCodename(),
                ]));
                continue;
            }

            $messageCodename = array_pop($messages);

            $processor = $this->processorFactory->create($messageCodename);

            // Get all users with selected schedule option for group
            $targets = $this->notification->findGroupFreqTargets($group, $freq);

            // Skip group if no targets had selected freq
            if (!$targets) {
                continue;
            }

            $this->logger->debug(':count targets found for scheduled notification group ":name"', [
                ':count' => count($targets),
                ':name'  => $group->getCodename(),
            ]);

            // Proceed message for each target
            foreach ($targets as $target) {
                $this->processMessage($processor, $messageCodename, $target);
            }
        }
    }

    private function processMessage(
        ScheduleProcessorInterface $processor,
        string $messageCodename,
        MessageTargetInterface $target
    ): void {
        $message = $this->notification->createMessage($messageCodename, $target, []);

        // Message does not need to be sent
        if (!$processor->makeMessage($message, $target)) {
            return;
        }

        $this->logger->info('Sending notification message ":name" to target ":who"', [
            ':name' => $messageCodename,
            ':who'  => $target->getEmail(),
        ]);

        $this->notification->enqueueScheduled($message);
    }
}
