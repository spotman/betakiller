<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1483435877_Ifaces_Label_Column extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1483435877;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'Ifaces_label_column';
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
	    if (!$this->table_has_column('ifaces', 'label')) {
	        $this->run_sql("ALTER TABLE `ifaces` ADD `label` varchar(32) COLLATE 'utf8_unicode_ci' NULL AFTER `codename`;");
        }
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{

	}

} // End Migration1483435877_Ifaces_Label_Column
