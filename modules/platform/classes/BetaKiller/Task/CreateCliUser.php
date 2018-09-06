<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Service\UserService;
use Psr\Log\LoggerInterface;

class CreateCliUser extends AbstractTask
{
    /**
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * CreateCliUser constructor.
     *
     * @param \BetaKiller\Service\UserService $userService
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(UserService $userService, LoggerInterface $logger)
    {
        $this->userService = $userService;
        $this->logger      = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No cli arguments
        return [];
    }

    public function run(): void
    {
        $user = $this->userService->createCliUser();

        if (!$user) {
            $this->logger->info('User [:name] already exists, exiting', [
                ':name' => $user->getUsername(),
            ]);
        } else {
            $this->logger->info('User [:name] successfully created', [
                ':name' => $user->getUsername(),
            ]);
        }
    }
}
