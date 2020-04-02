<?php

class Migration1585817969_Add_Missing_Index extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1585817969;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_missing_index';
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
	    $this->runSql('ALTER TABLE `stat_hit_pages` ADD UNIQUE `domain_id_uri` (`domain_id`, `uri`);');
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1585817969_Add_Missing_Index
