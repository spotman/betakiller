<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

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
        parent::__construct();

        $this->notification = $notification;
        $this->logger       = $logger;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [
            // No options
        ];
    }

    public function run(): void
    {
        $this->notification->broadcastMessage(self::NOTIFICATION_TEST_BROADCAST, []);

        $this->logger->info('Broadcast message sent');
    }
}
