<?php

class Migration1602839761_Add_Notification_Log_Read_At extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1602839761;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_notification_log_read_at';
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
	    if (!$this->tableHasColumn('notification_log', 'read_at', 'notifications')) {
	        $this->runSql("ALTER TABLE `notification_log` ADD read_at DATETIME DEFAULT NULL", Database::UPDATE, 'notifications');
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

} // End Migration1602839761_Add_Notification_Log_Read_At
