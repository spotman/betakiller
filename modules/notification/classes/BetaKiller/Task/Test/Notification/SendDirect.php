<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class SendDirect extends AbstractTask
{
    public const NOTIFICATION_TEST_DIRECT = 'developer/test/direct';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * SendDirect constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper          $notification
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        NotificationHelper $notification,
        UserRepositoryInterface $userRepo,
        LoggerInterface $logger
    ) {
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
            : $this->notification->debugEmailTarget();

        $this->notification->directMessage(self::NOTIFICATION_TEST_DIRECT, $target, []);

        $this->logger->debug('Message sent to ":email"', [
            ':email' => $target->getEmail(),
        ]);
    }
}
