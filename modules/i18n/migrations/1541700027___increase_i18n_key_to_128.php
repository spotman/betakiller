<?php

class Migration1541700027_Increase_I18n_Key_To_128 extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1541700027;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Increase_i18n_key_to_128';
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
	    $this->runSql("ALTER TABLE `i18n_keys` CHANGE `key` `key` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`;");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1541700027_Increase_I18n_Key_To_128
