<?php
declare(strict_types=1);

namespace BetaKiller\Task\Auth;

use BetaKiller\Repository\UserRepository;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ChangePassword extends AbstractTask
{
    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\UserRepository $userRepo
     */
    public function __construct(UserRepository $userRepo, LoggerInterface $logger)
    {
        $this->userRepo = $userRepo;
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

        $user = $this->userRepo->searchBy($username);

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

        $user->setPassword($password);

        $this->userRepo->save($user);

        $this->logger->info('Password successfully changed!');
    }
}