<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

final class SendBroadcast extends AbstractTask
{
    public const NOTIFICATION_TEST_BROADCAST = 'developer/test/broadcast';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Send constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper $notification
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        NotificationHelper $notification,
        LoggerInterface $logger
    ) {
        $this->notification = $notification;
        $this->logger       = $logger;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return array
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            // No options
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $this->notification->broadcastMessage(self::NOTIFICATION_TEST_BROADCAST, []);

        $this->logger->info('Broadcast message sent');
    }
}
