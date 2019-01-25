<?php

class Migration1548408948_Increase_Size_Of_Notification_Log_Target extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1548408948;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Increase_size_of_notification_log_target';
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
	    $this->runSql("ALTER TABLE `notification_log` CHANGE `target` `target` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `user_id`;");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1548408948_Increase_Size_Of_Notification_Log_Target
