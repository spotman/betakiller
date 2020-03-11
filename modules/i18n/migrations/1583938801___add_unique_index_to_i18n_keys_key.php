<?php

class Migration1583938801_Add_Unique_Index_To_I18n_Keys_Key extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1583938801;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_unique_index_to_i18n_keys_key';
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
	    $this->runSql('ALTER TABLE `i18n_keys` ADD UNIQUE `key` (`key`);');
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1583938801_Add_Unique_Index_To_I18n_Keys_Key
