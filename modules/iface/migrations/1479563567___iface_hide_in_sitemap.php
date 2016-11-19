<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1479563567_Iface_Hide_In_Sitemap extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1479563567;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'IFace_hide_in_sitemap';
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
        if (!$this->table_has_column('ifaces', 'hide_in_site_map'))
        {
            $this->run_sql("ALTER TABLE `ifaces` ADD `hide_in_site_map` BOOLEAN NOT NULL COMMENT 'hide iface in sitemap' AFTER `description`;");
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

} // End Migration1479563567_Iface_Hide_In_Sitemap
