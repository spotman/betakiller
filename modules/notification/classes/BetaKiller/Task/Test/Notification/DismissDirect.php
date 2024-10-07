<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

final class DismissDirect extends AbstractTask
{
    private const ARG_MESSAGE = 'message';
    private const ARG_TARGET  = 'target';

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
        $this->notification = $notification;
        $this->userRepo     = $userRepo;
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
            $builder->string(self::ARG_TARGET)->required()->label('User email'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $messageName = $params->getString(self::ARG_MESSAGE);
        $userEmail   = $params->getString(self::ARG_TARGET);

        $target = $this->userRepo->findByEmail($userEmail);

        $this->notification->dismissDirect($messageName, $target);

        $this->logger->debug('Direct message dismissed for :name', [
            ':name' => $target->getEmail(),
        ]);
    }
}
