<?php

class Widget_Admin_Bar extends \BetaKiller\IFace\Widget
{
    use \BetaKiller\Helper\CurrentUserTrait;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
    {
        $user = $this->current_user(true);

        // If user is not authorized, then silently exiting
        if (!$user || !$user->is_admin_allowed()) {
            return [];
        }

        return [
            'enabled' =>  true,
        ];
    }
}
