<?php
declare(strict_types=1);

namespace BetaKiller\Task\Auth;

use BetaKiller\Service\AuthService;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ChangePassword extends AbstractTask
{
    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(AuthService $auth, LoggerInterface $logger)
    {
        $this->auth   = $auth;
        $this->logger = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No cli arguments
        return [];
    }

    public function run(): void
    {
        $username = $this->read('Enter username or e-mail');

        $user = $this->auth->searchBy($username);

        if (!$user) {
            $this->logger->warning('No such user');

            return;
        }

        $password = $this->password('Enter new password');
        $confirm  = $this->password('Enter new password again');

        if ($password !== $confirm) {
            $this->logger->warning('Passwords are not identical');

            return;
        }

        $this->auth->updateUserPassword($user, $password);

        $this->logger->info('Password successfully changed!');
    }
}
