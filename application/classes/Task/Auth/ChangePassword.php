<?php

class Task_Auth_ChangePassword extends Minion_Task
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    protected function _execute(array $params)
    {
        $username = $this->read('Enter username or e-mail');

        $user = $this->userRepo->searchBy($username);

        if (!$user) {
            $this->write('No such user');
            return;
        }

        $password   = $this->password('Enter new password');
        $confirm    = $this->password('Enter new password again');

        if ($password !== $confirm) {
            $this->write('Passwords are not identical', self::COLOR_RED);
            return;
        }

        $user->setPassword($password);

        $this->userRepo->save($user);

        $this->write('Password successfully changed!', self::COLOR_GREEN);
    }
}
