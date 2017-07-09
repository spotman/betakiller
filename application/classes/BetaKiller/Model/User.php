<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use BetaKiller\Helper\RoleModelFactoryTrait;
use BetaKiller\Notification\NotificationUserInterface;

class User extends \Model_Auth_User implements UserInterface
{
    use RoleModelFactoryTrait;

    protected $_all_roles_ids = [];

    protected function _initialize(): void
    {
        $this->_table_name       = 'users';
        $this->_reload_on_wakeup = false;

        $this->belongs_to([
            'language' => [
                'model'       => 'Language',
                'foreign_key' => 'language_id',
            ],
        ]);

        $this->has_many([
            'ulogins' => [],
        ]);

        $this->load_with(['language']);

        parent::_initialize();
    }

    /**
     * @return Role
     * @throws \Kohana_Exception
     */
    protected function get_roles_relation(): Role
    {
        return $this->get('roles');
    }

    public function complete_login()
    {
        // Fetch all user roles and put it in cache
        $this->fetchAllUserRolesIDs();

        parent::complete_login();

//        if ($this->loaded()) {
//            $this->ip = sprintf("%u", ip2long(Request::$client_ip));
//            $this->session_id = Session::instance()->id();
//
//            $this->save();
//        }
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \Kohana_Exception
     */
    public function setUsername(string $value): UserInterface
    {
        return $this->set('username', $value);
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getUsername(): string
    {
        return $this->get('username');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \Kohana_Exception
     */
    public function setPassword(string $value): UserInterface
    {
        return $this->set('password', $value);
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getPassword(): string
    {
        return $this->get('password');
    }

    /**
     * @return bool
     */
    public function isDeveloper(): bool
    {
        return $this->hasRole($this->getRole(Role::DEVELOPER_ROLE_NAME));
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isModerator(): bool
    {
        return $this->hasRole($this->getRole(Role::MODERATOR_ROLE_NAME));
    }

    /**
     * @param string $role
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    private function getRole(string $role): RoleInterface
    {
        return $this->model_factory_role()->get_by_name($role);
    }

    /**
     * Returns true if current user is guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return ($this instanceof GuestUser);
    }

    /**
     * @todo Переписать на кешированный ACL ибо слишком затратно делать запрос в БД на проверку роли
     *
     * @param RoleInterface|string $role
     *
     * @return bool
     */
    public function hasRole(RoleInterface $role): bool
    {
        return $this->has('roles', $role);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface|string $role
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function addRole(RoleInterface $role): UserInterface
    {
        return $this->add('roles', $role);
    }

    /**
     * Get all user`s roles IDs
     *
     * @return array
     */
    public function getAllUserRolesIDs(): array
    {
        return $this->fetchAllUserRolesIDs();
    }

    protected function fetchAllUserRolesIDs()
    {
        // Caching coz it is very heavy operation without MPTT
        if (!$this->_all_roles_ids) {
            /** @var RoleInterface[] $roles */
            $roles = [];

            foreach ($this->get_roles_relation()->get_all() as $role) {
                $roles[] = [$role];
                $roles[] = $role->getAllParents();
            }

            $roles     = array_merge(...$roles);
            $roles_ids = [];

            foreach ($roles as $role) {
                $roles_ids[] = $role->get_id();
            }

            $this->_all_roles_ids = array_unique($roles_ids);
        }

        return $this->_all_roles_ids;
    }

    /**
     * Returns user`s language name
     *
     * @return string|null
     * @throws \Kohana_Exception
     */
    public function getLanguageName(): ?string
    {
        $lang_model = $this->getLanguage();

        $lang = ($this->loaded() && $lang_model->loaded())
            ? $lang_model->get_name()
            : null;

        return $lang;
    }

    /**
     * @return NULL|\Model_Language
     * @throws \Kohana_Exception
     */
    public function getLanguage(): ?\Model_Language
    {
        return $this->get('language');
    }

    /**
     * @todo сделать проверку соответствия ip-адреса тому, на который был выдан токен
     * @return bool
     */
    public function check_ip(): bool
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
        return true;
    }

    /**
     * Search for user by username or e-mail
     *
     * @param $username_or_email
     *
     * @return UserInterface|null
     * @throws \Kohana_Exception
     */
    public function searchBy($username_or_email): ?UserInterface
    {
        $user = $this->where($this->unique_key($username_or_email), '=', $username_or_email)->find();

        return $user->loaded() ? $user : null;
    }

    public function before_sign_in(): void
    {
        $this->check_is_active();
    }

    /**
     * @throws \Auth_Exception_Inactive
     * @throws \Kohana_Exception
     */
    protected function check_is_active(): void
    {
        // Проверяем активен ли аккаунт
        if (!$this->isActive()) {
            throw new \Auth_Exception_Inactive;
        }
    }

    /**
     * @throws \Auth_Exception_WrongIP
     * @throws \Auth_Exception_Inactive
     * @throws \Kohana_Exception
     */
    public function afterAutoLogin(): void
    {
        $this->check_is_active();

        // Проверяем IP-адрес
        if (!$this->check_ip()) {
            throw new \Auth_Exception_WrongIP;
        }
    }

    public function beforeSignOut(): void
    {
        // Empty by default
    }

    /**
     * Returns TRUE, if user account is switched on
     *
     * @return bool
     * @throws \Kohana_Exception
     */
    public function isActive(): bool
    {
        return ($this->loaded() && $this->get('is_active'));
    }

    /**
     * Filters only active users
     *
     * @return $this
     */
    public function filter_active()
    {
        return $this->where('is_active', '=', true);
    }

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getFirstName(): string
    {
        return (string)$this->get('first_name');
    }

    public function setFirstName(string $value): UserInterface
    {
        return $this->set('first_name', $value);
    }

    public function getLastName(): string
    {
        return (string)$this->get('last_name');
    }

    public function setLastName(string $value): UserInterface
    {
        return $this->set('last_name', $value);
    }

    public function getEmail(): string
    {
        return $this->get('email');
    }

    public function setEmail(string $value): UserInterface
    {
        return $this->set('email', $value);
    }

    /**
     * Возвращает основной номер телефона
     *
     * @return string
     * @throws \Kohana_Exception
     */
    public function getPhone(): string
    {
        return (string)$this->get('phone');
    }

    public function setPhone(string $number): UserInterface
    {
        return $this->set('phone', $number);
    }

    public function isEmailNotificationAllowed(): bool
    {
        return (bool)$this->get('notify_by_email');
    }

    public function isOnlineNotificationAllowed(): bool
    {
        // Online notification isn`t ready yet
        return false;
    }

    /**
     * @throws \Kohana_Exception
     */
    public function enableEmailNotification(): void
    {
        $this->set('notify_by_email', true);
    }

    /**
     * @throws \Kohana_Exception
     */
    public function disableEmailNotification(): void
    {
        $this->set('notify_by_email', false);
    }

    public function as_array(): array
    {
        return [
            'id'        => $this->get_id(),
            'username'  => $this->getUsername(),
            'email'     => $this->getEmail(),
            'firstName' => $this->getFirstName(),
            'lastName'  => $this->getLastName(),
            'phone'     => $this->getPhone(),
        ];
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getAccessControlIdentity(): string
    {
        return $this->getUsername();
    }

    /**
     * @return RoleInterface[]|\Traversable
     * @throws \Kohana_Exception
     */
    public function getAccessControlRoles()
    {
        return $this->get_roles_relation()->get_all();
    }

    /**
     * Forces authorization if user is not logged in
     *
     * @throws \HTTP_Exception_401
     * @return void
     */
    public function forceAuthorization(): void
    {
        if ($this->isGuest()) {
            throw new \HTTP_Exception_401();
        }
    }

    protected function getSerializableProperties()
    {
        return array_merge(parent::getSerializableProperties(), [
            '_all_roles_ids',
        ]);
    }
}
