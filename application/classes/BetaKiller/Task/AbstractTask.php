<?php
namespace BetaKiller\Task;

use BetaKiller\Model\UserInterface;

abstract class AbstractTask extends \Minion_Task
{
    const CLI_USER_NAME = 'minion';

    public static function getCliUserModel(): UserInterface
    {
        $username = self::CLI_USER_NAME;

        /** @var UserInterface $orm */
        $orm = \ORM::factory('User');

        $user = $orm->search_by($username);

        if (!$user->loaded()) {
            $password = microtime();

            $host  = parse_url(\Kohana::$base_url, PHP_URL_HOST);
            $email = $username.'@'.$host;

            /** @var UserInterface $user */
            $user = $orm
                ->set_username($username)
                ->set_password($password)
                ->set_email($email)
                ->disable_email_notification() // No notification for cron user
                ->create();

            // Allowing everything (admin may remove some roles later if needed)
            $user->add_all_available_roles();
        }

        return $user;
    }
}
