<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

final class DismissDirect extends AbstractTask
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
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * DismissDirect constructor.
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
            'message' => null,
            'target'  => null,
        ];
    }

    public function run(): void
    {
        $messageName = (string)$this->getOption('message', true);
        $userName    = (string)$this->getOption('target', true);

        $target = $this->userRepo->findByEmail($userName);

        $this->notification->dismissDirect($messageName, $target);

        $this->logger->debug('Direct message dismissed for :name', [
            ':name' => $target->getEmail(),
        ]);
    }
}
