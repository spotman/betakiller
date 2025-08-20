<?php

declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Notification\Envelope;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\ScheduleProcessor\ScheduleProcessorInterface;
use BetaKiller\Notification\ScheduleProcessorFactory;
use BetaKiller\Notification\ScheduleTargetSpecInterface;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;

final readonly class SendScheduled implements ConsoleTaskInterface
{
    private const ARG_FREQ = 'freq';

    /**
     * SendScheduled constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade          $notification
     * @param \BetaKiller\Notification\ScheduleProcessorFactory    $processorFactory
     * @param \BetaKiller\Notification\ScheduleTargetSpecInterface $targetSpec
     * @param \Psr\Log\LoggerInterface                             $logger
     */
    public function __construct(
        private NotificationFacade $notification,
        private ScheduleProcessorFactory $processorFactory,
        private ScheduleTargetSpecInterface $targetSpec,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_FREQ)->required(),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $freqCodename = $params->getString(self::ARG_FREQ);

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
                LoggerHelper::logRawException(
                    $this->logger,
                    new TaskException(
                        'Multiple messages in scheduled group ":name" are not allowed', [
                            ':name' => $group->getCodename(),
                        ]
                    )
                );
                continue;
            }

            $messageCodename = array_pop($messages);

            $processor = $this->processorFactory->create($messageCodename);

            // Get all users with selected schedule option for group
            $targets = $this->notification->getGroupTargets($group, $freq);

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
                if ($this->targetSpec->isAllowedTo($target)) {
                    $this->processMessage($processor, $messageCodename, $target);
                }
            }
        }
    }

    private function processMessage(
        ScheduleProcessorInterface $processor,
        string $messageCodename,
        MessageTargetInterface $target
    ): void {
        $message = $this->notification->createMessage($messageCodename);

        // Message does not need to be sent
        if (!$processor->fillUpMessage($message, $target)) {
            return;
        }

        $this->logger->info('Sending notification message ":name" to target ":who"', [
            ':name' => $messageCodename,
            ':who'  => $target->getMessageIdentity(),
        ]);

        $this->notification->enqueueScheduled(new Envelope($target, $message));
    }
}
