<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Notification_Message extends Kohana_Notification_Message {

    public function to_developers()
    {
        /** @var Model_User $user_orm */
        $user_orm = ORM::factory('User');

        $developers = $user_orm->get_developers_list();

        return $this->to_users($developers);
    }

    public function to_moderators()
    {
        /** @var Model_User $user_orm */
        $user_orm = ORM::factory('User');

        $moderators = $user_orm->get_moderators_list();

        return $this->to_users($moderators);
    }

    public function to_users($users)
    {
        foreach ( $users as $user )
        {
            $this->set_to($user);
        }

        return $this;
    }

    public function to_current_user()
    {
        $this->set_to(Env::user());
        return $this;
    }

    protected function template_factory()
    {
        return Twig::factory();
    }

}
