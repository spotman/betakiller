<?php

class Migration1540063861_Add_Languages_Is_System extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1540063861;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_languages_is_system';
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
	    if (!$this->tableHasColumn('languages', 'is_system')) {
	        $this->runSql("ALTER TABLE `languages` ADD `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean marker, 1 means language is used for i18n and other system-wide stuff';");
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

} // End Migration1540063861_Add_Languages_Is_System
