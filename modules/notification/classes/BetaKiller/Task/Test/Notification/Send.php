<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Error\PhpExceptionStorageHandler;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Task\AbstractTask;

class Send extends AbstractTask
{
    public const NOTIFICATION_TEST = 'developer/test';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * Send constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper $notification
     * @param \BetaKiller\Repository\UserRepository $userRepo
     */
    public function __construct(NotificationHelper $notification, UserRepository $userRepo)
    {
        $this->notification = $notification;
        $this->userRepo     = $userRepo;

        parent::__construct();
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
            'targetUser' => null,
        ];
    }

    public function run(): void
    {
        $userID = (string)$this->getOption('targetUser', false);

        $target = $userID
            ? $this->userRepo->getById($userID)
            : PhpExceptionStorageHandler::getNotificationTarget($this->notification);

        $this->notification->directMessage(self::NOTIFICATION_TEST, $target, []);
    }
}
