<?php
declare(strict_types=1);

namespace BetaKiller\Task\Auth;

use BetaKiller\Repository\UserRepository;

class ChangePassword extends \BetaKiller\Task\AbstractTask
{
    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\UserRepository $userRepo
     */
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;

        parent::__construct();
    }

    public function run(): void
    {
        $username = $this->read('Enter username or e-mail');

        $user = $this->userRepo->searchBy($username);

        if (!$user) {
            $this->write('No such user');

            return;
        }

        $password = $this->password('Enter new password');
        $confirm  = $this->password('Enter new password again');

        if ($password !== $confirm) {
            $this->write('Passwords are not identical', self::COLOR_RED);

            return;
        }

        $user->setPassword($password);

        $this->userRepo->save($user);

        $this->write('Password successfully changed!', self::COLOR_GREEN);
    }
}
