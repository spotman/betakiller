<?php

class Migration1546931869_Add_Notification_Log_Lang extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1546931869;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_notification_log_lang';
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
	    if (!$this->tableHasColumn('notification_log', 'lang')) {
	        $this->runSql('ALTER TABLE `notification_log` ADD `lang` varchar(2) COLLATE "utf8_unicode_ci" NOT NULL AFTER `target`;');
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

} // End Migration1546931869_Add_Notification_Log_Lang
