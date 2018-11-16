<?php

class Migration1542401820_Allow_Null_In_Notification_Log_Result extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1542401820;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Allow_null_in_notification_log_result';
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
	    $this->runSql("ALTER TABLE `notification_log` CHANGE `result` `result` varchar(255) COLLATE 'utf8_unicode_ci' NULL AFTER `body`;");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1542401820_Allow_Null_In_Notification_Log_Result
