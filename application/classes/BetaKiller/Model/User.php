<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;

class User extends \Model_Auth_User implements UserInterface
{
    protected $_table_name = 'users';

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
     * @return RoleInterface
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
        return $this->has_role(Role::DEVELOPERS_ROLE_NAME);
    }

    /**
     * @return bool
     */
    public function is_moderator()
    {
        return $this->has_role(Role::MODERATORS_ROLE_NAME);
    }

    /**
     * @return bool
     */
    protected function is_writer()
    {
        return $this->has_role(Role::WRITER_ROLE_NAME);
    }

    /**
     * @todo Переписать на кешированный ACL ибо слишком затратно делать запрос в БД на проверку роли
     *
     * @param RoleInterface|string $role
     * @return bool
     */
    public function has_role($role)
    {
        $role = $this->prepare_role_object($role);

        return $this->has('roles', $role);
    }

    public function add_role($role)
    {
        $role = $this->prepare_role_object($role);

        return $this->add('roles', $role);
    }

    /**
     * @param string|RoleInterface $role
     *
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Exception
     */
    protected function prepare_role_object($role)
    {
        if (is_string($role)) {
            /** @var RoleInterface $orm */
            $orm = \ORM::factory('Role');
            $role = $orm->get_by_name($role);
        }

        if ( !($role instanceof RoleInterface) ) {
            throw new Exception('Role object must be instance of :needs', [':needs' => RoleInterface::class]);
        }

        return $role;
    }

    /**
     * @return $this
     */
    public function add_all_available_roles()
    {
        /** @var RoleInterface $orm */
        $orm = \ORM::factory('Role');
        $roles = $orm->find_all();

        foreach ($roles as $role) {
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

        return $lang;
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
        $this->check_is_active();
    }

    protected function check_is_active()
    {
        // Проверяем активен ли аккаунт
        if ( !$this->is_active() )
            throw new \Auth_Exception_Inactive;
    }

    public function after_auto_login()
    {
        $this->check_is_active();

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
        return $this->get_first_name().' '.$this->get_last_name();
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
     * @return \Database_Result|UserInterface[]
     */
    public function get_developers_list()
    {
        /** @var RoleInterface $roles_orm */
        $roles_orm = \ORM::factory('Role');

        return $roles_orm->developers()->get_users()->find_all();
    }

    /**
     * @return \Database_Result|UserInterface[]
     */
    public function get_moderators_list()
    {
        /** @var RoleInterface $roles_orm */
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
        return ($this->is_moderator() || $this->is_developer() || $this->is_writer());
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
