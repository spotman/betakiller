<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class SendDirect extends AbstractTask
{
    private const ARG_TARGET = 'target';

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
            $builder->string(self::ARG_TARGET)->optional()->label('User email'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $userEmail = $params->has(self::ARG_TARGET)
            ? $params->getString(self::ARG_TARGET)
            : null;

        $target = $userEmail
            ? $this->userRepo->findByEmail($userEmail)
            : $this->notification->debugEmailTarget();

        $this->notification->directMessage(self::NOTIFICATION_TEST_DIRECT, $target, []);

        $this->logger->info('Message sent to ":email"', [
            ':email' => $target->getEmail(),
        ]);
    }
}
