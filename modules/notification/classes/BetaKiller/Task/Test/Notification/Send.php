<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Error\PhpExceptionStorageHandler;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Send constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper $notification
     * @param \BetaKiller\Repository\UserRepository $userRepo
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(NotificationHelper $notification, UserRepository $userRepo, LoggerInterface $logger)
    {
        parent::__construct();

        $this->notification = $notification;
        $this->userRepo     = $userRepo;
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
            'target' => null,
        ];
    }

    public function run(): void
    {
        $userName = (string)$this->getOption('target', false);

        $target = $userName
            ? $this->userRepo->searchBy($userName)
            : PhpExceptionStorageHandler::getNotificationTarget($this->notification);

        $this->notification->directMessage(self::NOTIFICATION_TEST, $target, []);

        $this->logger->debug('Message sent to ":email"', [
            ':email' => $target->getEmail(),
        ]);
    }
}
