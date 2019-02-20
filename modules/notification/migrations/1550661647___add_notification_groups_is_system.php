<?php

class Migration1550661647_Add_Notification_Groups_Is_System extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1550661647;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_notification_groups_is_system';
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
	    if (!$this->tableHasColumn('notification_groups', 'is_system')) {
	        $this->runSql("ALTER TABLE `notification_groups` ADD `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `is_enabled`;");
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

} // End Migration1550661647_Add_Notification_Groups_Is_System
