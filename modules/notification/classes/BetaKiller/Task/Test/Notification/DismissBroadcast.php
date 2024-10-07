<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

final class DismissBroadcast extends AbstractTask
{
    private const ARG_MESSAGE = 'message';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * DismissBroadcast constructor.
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
            $builder->string(self::ARG_MESSAGE)->required()->label('Message codename'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $messageName = $params->getString(self::ARG_MESSAGE);

        $this->notification->dismissBroadcast($messageName);

        $this->logger->debug('Broadcast message dismissed');
    }
}
