<?php

class Migration1583328138_Add_Stat_Hits_Is_Protected extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1583328138;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_stat_hits_is_protected';
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
	    if (!$this->tableHasColumn('stat_hits', 'is_protected')) {
            $this->runSql("ALTER TABLE `stat_hits` ADD `is_protected` tinyint(1) unsigned NOT NULL DEFAULT '0';");

            $this->runSql('UPDATE stat_hits AS sh
LEFT JOIN users AS u ON sh.id = u.first_hit_id
SET sh.is_protected = 1
WHERE u.first_hit_id IS NOT NULL;');
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

} // End Migration1583328138_Add_Stat_Hits_Is_Protected
