<?php

class Migration1603957959_Fix_Reserved_Words extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1603957959;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Fix_reserved_words';
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
	    if ($this->tableHasColumn('i18n_keys', 'key')) {
	        $this->runSql("ALTER TABLE `i18n_keys`
CHANGE `key` `codename` varchar(128) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
COLLATE 'utf8mb4_unicode_ci';");
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

} // End Migration1603957959_Fix_Reserved_Words
