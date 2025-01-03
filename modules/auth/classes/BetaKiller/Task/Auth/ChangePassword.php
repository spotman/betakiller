<?php

declare(strict_types=1);

namespace BetaKiller\Task\Auth;

use BetaKiller\Console\ConsoleHelper;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ChangePassword extends AbstractTask
{
    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Service\AuthService                $auth
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private AuthService $auth,
        private LoggerInterface $logger)
    {
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        // No cli arguments
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $username = ConsoleHelper::read('Enter username or e-mail');

        $user = $this->userRepo->searchBy($username);

        if (!$user) {
            $this->logger->warning('No such user');

            return;
        }

        $password = ConsoleHelper::password('Enter new password');
        $confirm  = ConsoleHelper::password('Enter new password again');

        if ($password !== $confirm) {
            $this->logger->warning('Passwords are not identical');

            return;
        }

        $this->auth->updateUserPassword($user, $password);

        $this->logger->info('Password successfully changed!');
    }
}
