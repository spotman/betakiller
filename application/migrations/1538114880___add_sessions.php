<?php

class Migration1538114880_Add_Sessions extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1538114880;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_sessions';
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
	    if(!$this->tableExists('sessions')) {
	        $this->runSql('CREATE TABLE `sessions` (
  `session_id` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `last_active` int(10) unsigned NOT NULL,
  `contents` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_active` (`last_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
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

} // End Migration1538114880_Add_Sessions
