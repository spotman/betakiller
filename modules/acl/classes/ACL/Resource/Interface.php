<? defined('SYSPATH') OR die('No direct script access.');

interface ACL_Resource_Interface
{
	/**
	 * Returns the string identifier of the Resource
	 *
	 * @return string
	 */
	public function get_resource_id();
}
