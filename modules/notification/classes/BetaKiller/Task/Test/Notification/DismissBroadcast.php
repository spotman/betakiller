<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

final class DismissBroadcast extends AbstractTask
{
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
            'message' => null,
        ];
    }

    public function run(): void
    {
        $messageName = (string)$this->getOption('message', true);

        $this->notification->dismissBroadcast($messageName);

        $this->logger->debug('Broadcast message dismissed');
    }
}
