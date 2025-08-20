<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Helper\NotificationGatewayInterface;
use BetaKiller\Notification\Message\DeveloperTestBroadcastMessage;
use Psr\Log\LoggerInterface;

final readonly class SendBroadcast implements ConsoleTaskInterface
{
    public const NOTIFICATION_TEST_BROADCAST = 'developer/test/broadcast';

    /**
     * SendBroadcast constructor.
     *
     * @param \BetaKiller\Helper\NotificationGatewayInterface $notification
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(
        private NotificationGatewayInterface $notification,
        private LoggerInterface $logger
    ) {
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
        $this->notification->sendBroadcast(DeveloperTestBroadcastMessage::create());

        $this->logger->info('Broadcast message sent');
    }
}
