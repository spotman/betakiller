<?php
namespace BetaKiller\Model;

class User extends \Model_Auth_User implements \Notification_User_Interface //implements ACL_Role_Interface
{
    protected $_reload_on_wakeup = FALSE;

    protected function _initialize()
    {
        $this->belongs_to(array(
            'language' => array(
                'model' => 'Language',
                'foreign_key' => 'language_id',
            ),
        ));

        $this->has_many(array(
            'ulogins' => array(),
        ));

        $this->load_with(['language']);

        parent::_initialize();
    }

    /**
     * @return \Model_Role
     */
    protected function get_roles_relation()
    {
        return $this->get('roles');
    }

//    public function complete_login()
//    {
//        parent::complete_login();
//
//        if ( $this->loaded() )
//        {
//        $this->date_login = date('Y-m-d H:i:s');
//        $this->ip = sprintf("%u", ip2long(Request::$client_ip));
//        $this->session_id = Session::instance()->id();
//
//        $this->save();
//
//        }
//    }

    /**
     * @param string $value
     * @return $this
     */
    public function set_username($value)
    {
        return $this->set('username', $value);
    }

    /**
     * @return string
     */
    public function get_username()
    {
        return $this->get('username');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function set_password($value)
    {
        return $this->set('password', $value);
    }

    /**
     * @return string
     */
    public function get_password()
    {
        return $this->get('password');
    }

    /**
     * @return bool
     */
    public function is_developer()
    {
        return $this->has_role(\Model_Role::DEVELOPERS_ROLE_NAME);
    }

    /**
     * @return bool
     */
    public function is_moderator()
    {
        return $this->has_role(\Model_Role::MODERATORS_ROLE_NAME);
    }

    /**
     * @todo Переписать на кешированный ACL ибо слишком затратно делать запрос в БД на проверку роли
     *
     * @param \Model_Role|string $role
     * @return bool
     */
    public function has_role($role)
    {
        if ( ! ($role instanceof \Model_Role) )
        {
            /** @var \Model_Role $orm */
            $orm = \ORM::factory('Role');
            $role = $orm->get_by_name($role);
        }

        return $this->has('roles', $role);
    }

    public function add_developers_role()
    {
        return $this->add_role(\Model_Role::DEVELOPERS_ROLE_NAME);
    }

    public function add_role($role)
    {
        if ( ! ($role instanceof \Model_Role) )
        {
            /** @var \Model_Role $orm */
            $orm = \ORM::factory('Role');
            $role = $orm->get_by_name($role);
        }

        return $this->add('roles', $role);
    }

    public function add_all_available_roles()
    {
        /** @var \Model_Role $orm */
        $orm = \ORM::factory('Role');
        $roles = $orm->find_all();

        foreach ($roles as $role)
        {
            $this->add_role($role);
        }

        return $this;
    }

    /**
     * Get all user`s roles IDs
     *
     * @todo Store this data in session
     * @return array
     */
    public function get_roles_ids()
    {
        return $this->get_roles_relation()->find_all_ids();
    }

    /**
     * Возвращает имя языка, назначенного пользователю
     * @return string
     */
    public function get_language_name()
    {
        $lang_model = $this->get_language();

        $lang = ( $this->loaded() AND $lang_model->loaded() )
            ? $lang_model->get_name()
            : NULL;

        return $lang ?: "ru";
    }

    /**
     * @return NULL|\Model_Language
     */
    public function get_language()
    {
        return $this->get('language');
    }

    /**
     * @todo сделать проверку соответствия ip-адреса тому, на который был выдан токен
     * @return bool
     */
    public function check_ip()
    {
//        $ip = Request::client_ip();
//        $client_ip = ip2long($this->get_real_ip_address());
//
//        if ( ! (($client_ip >= ip2long('10.0.0.0') AND $client_ip < ip2long('10.255.255.255')) OR
//            ($client_ip >= ip2long('172.16.0.0') AND $client_ip < ip2long('172.31.255.255')) OR
//            ($client_ip >= ip2long('192.168.0.0') AND $client_ip < ip2long('192.168.255.255')) OR
//            $_SERVER['REMOTE_ADDR'] == '127.0.0.1'))
//        {
//            return FALSE;
//        }
//
        return TRUE;
    }

    /**
     * Search for user by username or e-mail
     * @param $username_or_email
     * @throws \HTTP_Exception_403
     * @return \ORM
     */
    public function search_by($username_or_email)
    {
        return $this->where($this->unique_key($username_or_email), '=', $username_or_email)->find();
    }

    public function before_sign_in()
    {
        // Проверяем активен ли аккаунт
        if ( ! $this->is_active() )
            throw new \Auth_Exception_Inactive;
    }

    public function after_auto_login()
    {
        // Проверяем IP-адрес
        if ( ! $this->check_ip() )
            throw new \Auth_Exception_WrongIP;
    }

    public function before_sign_out()
    {
        // Empty by default
    }

    /**
     * Returns TRUE, if user account is switched on
     *
     * @return bool
     */
    public function is_active()
    {
        return ( $this->loaded() AND $this->get('is_active') );
    }

    /**
     * Returns TRUE if user is logged in now
     *
     * @return bool
     * @throws \HTTP_Exception_501
     */
    public function is_logged_in()
    {
        throw new \HTTP_Exception_501('Not implemented yet');
    }

    /**
     * Filters only active users
     *
     * @return $this
     */
    public function filter_active()
    {
        return $this->where('is_active', '=', TRUE);
    }

    public function get_full_name()
    {
        return $this->get_first_name() .' '. $this->get_last_name();
    }

    public function get_first_name()
    {
        return $this->get('first_name');
    }

    public function set_first_name($value)
    {
        return $this->set('first_name', $value);
    }

    public function get_last_name()
    {
        return $this->get('last_name');
    }

    public function set_last_name($value)
    {
        return $this->set('last_name', $value);
    }

    public function get_email()
    {
        return $this->get('email');
    }

    public function set_email($value)
    {
        return $this->set('email', $value);
    }

    /**
     * Возвращает основной номер телефона
     *
     * @return string
     */
    public function get_phone()
    {
        return $this->get('phone');
    }

    public function set_phone($number)
    {
        return $this->set('phone', $number);
    }

    /**
     * @return \Database_Result|\Model_User[]
     */
    public function get_developers_list()
    {
        /** @var \Model_Role $roles_orm */
        $roles_orm = \ORM::factory('Role');

        return $roles_orm->developers()->get_users()->find_all();
    }

    /**
     * @return \Database_Result|\Model_User[]
     */
    public function get_moderators_list()
    {
        /** @var \Model_Role $roles_orm */
        $roles_orm = \ORM::factory('Role');

        return $roles_orm->moderators()->get_users()->find_all();
    }

    public function is_email_notification_allowed()
    {
        return TRUE;
    }

    public function is_online_notification_allowed()
    {
        return FALSE;
    }

    /**
     * Возвращает true если пользователю разрешено использовать админку
     * @return bool
     */
    public function is_admin_allowed()
    {
        return ($this->is_moderator() OR $this->is_developer());
    }

    public function as_array()
    {
        return [
            'id'        =>  $this->get_id(),
            'username'  =>  $this->get_username(),
            'email'     =>  $this->get_email(),
            'firstName' =>  $this->get_first_name(),
            'lastName'  =>  $this->get_last_name(),
            'phone'     =>  $this->get_phone(),
        ];
    }

}
