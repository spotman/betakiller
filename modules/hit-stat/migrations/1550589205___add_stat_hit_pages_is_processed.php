<?php

class Migration1550589205_Add_Stat_Hit_Pages_Is_Processed extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1550589205;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_stat_hit_pages_is_processed';
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
	    if ($this->tableHasColumn('stat_hits', 'processed')) {
	        $this->runSql("ALTER TABLE `stat_hits` CHANGE `processed` `is_processed` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `ip`;");
        }
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1550589205_Add_Stat_Hit_Pages_Is_Processed
