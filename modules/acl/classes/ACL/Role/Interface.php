<? defined('SYSPATH') OR die('No direct script access.');

interface ACL_Role_Interface
{
	/**
	 * Returns the string identifier of the Role
	 *
	 * @return string
	 */
	public function get_role_id();
}
