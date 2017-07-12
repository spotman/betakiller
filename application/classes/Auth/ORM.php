<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_ORM extends Kohana_Auth_ORM
{
    /**
     * Logs a user in.
     *
     * @param   string|\BetaKiller\Model\UserInterface $user
     * @param   string            $password
     * @param   boolean           $remember enable autologin
     *
     * @return  boolean
     * @throws Auth_Exception
     */
    protected function _login($user, $password, $remember)
    {
        if (!is_object($user)) {
            $username = $user;

            /** @var \BetaKiller\Model\User $orm */
            $orm  = ORM::factory('User');
            $user = $orm->searchBy($username);

            if (!$user || !$user->loaded()) {
                throw new Auth_Exception_UserDoesNotExists;
            }
        }

        $user->beforeSignIn();

        if (!parent::_login($user, $password, $remember)) {
            throw new Auth_Exception_IncorrectPassword;
        }

        return true;
    }

    public function logout($destroy = false, $logout_all = false)
    {
        /** @var Model_User $user */
        $user = $this->get_user();

        if (!$user) {
            return false;
        }

        $user->beforeSignOut();

        return parent::logout($destroy, $logout_all);
    }

    public function auto_login()
    {
        /** @var Model_User $user */
        $user = parent::auto_login();

        if ($user) {
            $user->afterAutoLogin();
        }

        return $user;
    }
}
