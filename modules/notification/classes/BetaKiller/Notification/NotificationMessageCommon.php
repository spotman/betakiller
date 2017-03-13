<?php
namespace BetaKiller\Notification;

use BetaKiller\Helper\CurrentUserTrait;
use BetaKiller\Helper\UserModelFactoryTrait;
use Twig;

class NotificationMessageCommon extends NotificationMessageAbstract
{
    use CurrentUserTrait;
    use UserModelFactoryTrait;

    public function to_developers()
    {
        $developers = $this->model_factory_user()->get_developers_list();

        return $this->to_users($developers);
    }

    public function to_moderators()
    {
        $moderators = $this->model_factory_user()->get_moderators_list();

        return $this->to_users($moderators);
    }

    public function to_current_user()
    {
        $this->set_to($this->current_user());

        return $this;
    }

    protected function template_factory()
    {
        return Twig::factory();
    }
}
