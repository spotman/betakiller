<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1474879723_Iface_Is_Tree_Field extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1474879723;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'IFace_is_tree_field';
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
	    $this->run_sql("ALTER TABLE `ifaces` ADD `is_tree` int(1) NULL COMMENT 'IFace has multi-level tree structure' AFTER `is_dynamic`; -- 0.032 s");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{

	}

} // End Migration1474879723_Iface_Is_Tree_Field
