<?php defined('SYSPATH') OR die('No direct script access.');


class Task_Auth_PasswordHash extends Minion_Task {

    protected function _execute(array $params)
    {
        $password = Minion_CLI::read("Enter the password");

        $hash = Auth::instance()->hash($password);

        Minion_CLI::write(
            Minion_CLI::color($hash, "green")
        );
    }

}
