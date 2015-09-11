<?php defined('SYSPATH') OR die('No direct script access.');


class Task_Auth_PasswordHash extends Minion_Task {

    protected function _execute(array $params)
    {
//        $password   = Minion_CLI::read("Enter the password");
//        $confirm    = Minion_CLI::read("Enter the password again");

        $password   = $this->password("Enter the password");
        $confirm    = $this->password("Enter the password again");

        if ( $password != $confirm )
        {
            $this->write('Passwords are not identical', self::RED);
            return;
        }

        $hash = Auth::instance()->hash($password);

        $this->write($hash, self::GREEN);
    }

}
