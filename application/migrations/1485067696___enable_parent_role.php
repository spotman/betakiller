<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1485067696_Enable_Parent_Role extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1485067696;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'Enable_parent_role';
	}

	/**
	 * Returns migration info
	 *
	 * @return string
	 */
	public function description()
	{
		return '';
	}

	/**
	 * Takes a migration
	 *
	 * @return void
	 */
	public function up()
	{
	    if (!$this->table_has_column('roles', 'parent_id')) {
	        $this->addParentId();
        }
	}

	protected function addParentId()
    {
        $this->run_sql("
        ALTER TABLE `roles`
        ADD `parent_id` int(11) unsigned NULL AFTER `id`,
        ADD INDEX `parent_id` (`parent_id`),
        ADD FOREIGN KEY (`parent_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
        ;");
    }

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{

	}

} // End Migration1485067696_Enable_Parent_Role
