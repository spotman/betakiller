<?php
namespace BetaKiller\Model;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Notification\NotificationUserInterface;
use Spotman\Acl\AclUserInterface;

interface UserInterface extends OrmInterface, NotificationUserInterface, AclUserInterface
{
    // Auth_ORM methods
    public function complete_login();


    // Extended methods

    /**
     * @param string $value
     * @return $this
     */
    public function set_username($value);

    /**
     * @return string
     */
    public function get_username();

    /**
     * @param string $value
     * @return $this
     */
    public function set_password($value);

    /**
     * @return string
     */
    public function get_password();

    /**
     * @return bool
     */
    public function is_developer();

    /**
     * @return bool
     */
    public function is_moderator();

    /**
     * @param RoleInterface|string $role
     * @return bool
     */
    public function has_role($role);

    /**
     * @param string|RoleInterface $role
     *
     * @return $this
     */
    public function add_role($role);

    /**
     * @return $this
     */
    public function add_all_available_roles();

    /**
     * Get all user`s roles IDs
     *
     * @return int[]
     */
    public function get_roles_ids();

    /**
     * Возвращает имя языка, назначенного пользователю
     * @return string
     */
    public function get_language_name();

    /**
     * @return NULL|\Model_Language
     */
    public function get_language();

    /**
     * Search for user by username or e-mail
     * @param $username_or_email
     * @throws \HTTP_Exception_403
     * @return UserInterface
     */
    public function search_by($username_or_email);

    public function before_sign_in();

    /**
     * @return void
     * @throws \Auth_Exception_WrongIP
     */
    public function after_auto_login();

    /**
     * @return void
     */
    public function before_sign_out();

    /**
     * Returns TRUE, if user account is switched on
     *
     * @return bool
     */
    public function is_active();

    /**
     * Returns TRUE if user is logged in now
     *
     * @return bool
     */
    public function is_logged_in();

    /**
     * Filters only active users
     *
     * @return $this
     */
    public function filter_active();

    /**
     * @return string
     */
    public function get_full_name();

    /**
     * @return string
     */
    public function get_first_name();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_first_name($value);

    /**
     * @return string
     */
    public function get_last_name();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_last_name($value);

    /**
     * @return string
     */
    public function get_email();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_email($value);

    /**
     * Возвращает основной номер телефона
     *
     * @return string
     */
    public function get_phone();

    /**
     * @param string $number
     *
     * @return $this
     */
    public function set_phone($number);

    /**
     * @return \Database_Result|UserInterface[]
     */
    public function get_developers_list();

    /**
     * @return \Database_Result|UserInterface[]
     */
    public function get_moderators_list();

    /**
     * Возвращает true если пользователю разрешено использовать админку
     * Use \BetaKiller\Acl\Resource\AdminResource instead or domain-specific Acl resource
     * @return bool
     * @deprecated
     */
    public function is_admin_allowed();

    /**
     * @return array
     */
    public function as_array();
}
