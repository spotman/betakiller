<?php

class Migration1533201128_Add_Languages_Locale extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1533201128;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_languages_locale';
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
	    if (!$this->tableHasColumn('languages', 'locale')) {
	        $this->runSql("ALTER TABLE `languages` ADD `locale` varchar(8) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Locale string' AFTER `name`;");
	        $this->runSql("UPDATE `languages` SET `locale` = 'en-GB' WHERE `name` = 'en';");
	        $this->runSql("UPDATE `languages` SET `locale` = 'de-DE' WHERE `name` = 'de';");
	        $this->runSql("UPDATE `languages` SET `locale` = 'ru-RU' WHERE `name` = 'ru';");
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

} // End Migration1533201128_Add_Languages_Locale
