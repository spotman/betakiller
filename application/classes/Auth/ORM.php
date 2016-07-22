<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_ORM extends Kohana_Auth_ORM {

    /**
     * Logs a user in.
     *
     * @param   string|Model_User   $user
     * @param   string   $password
     * @param   boolean  $remember  enable autologin
     * @return  boolean
     * @throws Auth_Exception
     */
    protected function _login($user, $password, $remember)
    {
        if ( ! is_object($user) )
        {
            $username = $user;

            /** @var Model_User $orm */
            $orm = ORM::factory('User');
            $user = $orm->search_by($username);

            if ( ! $user->loaded() )
                throw new Auth_Exception_UserDoesNotExists;
        }

        $user->before_sign_in();

        if( ! parent::_login($user, $password, $remember) )
            throw new Auth_Exception_IncorrectPassword;

        return TRUE;
    }

    public function logout($destroy = FALSE, $logout_all = FALSE)
    {
        /** @var Model_User $user */
        $user = $this->get_user();

        if ( ! $user )
            return FALSE;

        $user->before_sign_out();

        return parent::logout($destroy, $logout_all);
    }

    public function auto_login()
    {
        /** @var Model_User $user */
        $user = parent::auto_login();

        if ( $user )
        {
            $user->after_auto_login();
        }

        return $user;
    }

}
