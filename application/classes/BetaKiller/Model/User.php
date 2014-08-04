<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Model_User extends Model_Auth_User implements Notification_User_Interface //implements ACL_Role_Interface
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

        parent::_initialize();
    }

    /**
     * @return Model_Role
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

    public function get_username()
    {
        return $this->get('username');
    }

    public function get_password()
    {
        return $this->get('password');
    }

    /**
     * @return bool
     */
    public function is_developer()
    {
        return $this->has_role('developer');
    }

    /**
     * @return bool
     */
    public function is_moderator()
    {
        return $this->has_role('moderator');
    }

    /**
     * @todo Переписать на кешированный ACL ибо слишком затратно делать запрос в БД на проверку роли
     *
     * @param Model_Role|string $role
     * @return bool
     */
    public function has_role($role)
    {
        if ( ! ($role instanceof Model_Role) )
        {
            /** @var Model_Role $orm */
            $orm = ORM::factory('Role');
            $role = $orm->get_by_name($role);
        }

        return $this->has('roles', $role);
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
     * @return NULL|Model_Language
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
     * @throws HTTP_Exception_403
     */
    public function search_by($username_or_email)
    {
        $this->where($this->unique_key($username_or_email), '=', $username_or_email)->find();
    }

    public function before_sign_in()
    {
        // Проверяем активен ли аккаунт
        if ( ! $this->is_active() )
            throw new Auth_Exception_Inactive;
    }

    public function after_auto_login()
    {
        // Проверяем IP-адрес
        if ( ! $this->check_ip() )
            throw new Auth_Exception_WrongIP;
    }

    public function before_sign_out()
    {
        // Empty by default
    }

    /**
     * Возвращает TRUE, если аккаунт пользователя включён
     * @return bool
     */
    public function is_active()
    {
        return ( $this->loaded() AND $this->get('is_active') );
    }

    public function get_full_name()
    {
        return $this->get_first_name() .' '. $this->get_last_name();
    }

    public function get_first_name()
    {
        return $this->get('first_name');
    }

    public function get_last_name()
    {
        return $this->get('last_name');
    }

    public function get_email()
    {
        return $this->get('email');
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
     * @return Database_Result|Model_User[]
     */
    public function get_developers_list()
    {
        /** @var Model_Role $roles_orm */
        $roles_orm = ORM::factory('Role');

        return $roles_orm->developers()->get_users()->find_all();
    }

    /**
     * @return Database_Result|Model_User[]
     */
    public function get_moderators_list()
    {
        /** @var Model_Role $roles_orm */
        $roles_orm = ORM::factory('Role');

        return $roles_orm->moderators()->get_users()->find_all();
    }

    public function is_online()
    {
        return FALSE;
    }

    public function is_email_notification_allowed()
    {
        return TRUE;
    }

    public function is_online_notification_allowed()
    {
        return FALSE;
    }

}
