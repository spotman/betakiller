<?php

class Migration1567777450_Increase_Hit_Stat_Ip_Address_Column_For_Ipv6 extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1567777450;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Increase_hit_stat_ip_address_column_for_ipv6';
	}

	/**
	 * Returns migration info
	 *
	 * @return string
	 */
	public function description(): string
	{
		return '';
	}

	/**
	 * Takes a migration
	 *
	 * @return void
	 */
	public function up(): void
	{
	    $this->runSql("ALTER TABLE `stat_hits` CHANGE `ip` `ip` varchar(46) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `user_id`;");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1567777450_Increase_Hit_Stat_Ip_Address_Column_For_Ipv6
