<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Helper\NotificationGatewayInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

final readonly class SendDirect implements ConsoleTaskInterface
{
    private const ARG_TARGET = 'target';

    public const NOTIFICATION_TEST_DIRECT = 'developer/test/direct';

    /**
     * SendDirect constructor.
     *
     * @param \BetaKiller\Helper\NotificationGatewayInterface $notification
     * @param \BetaKiller\Repository\UserRepositoryInterface  $userRepo
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(
        private NotificationGatewayInterface $notification,
        private UserRepositoryInterface $userRepo,
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
