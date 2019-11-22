<?php

class Migration1574163640_Remove_Unused_User_Statuses extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1574163640;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Remove_unused_user_statuses';
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
	    if ($this->tableHasColumnValue('user_statuses', 'codename', 'verified')) {
	        $this->runSql('DELETE FROM `user_statuses` WHERE `codename` = "verified" LIMIT 1;');
        }

	    if ($this->tableHasColumnValue('user_statuses', 'codename', 'approved')) {
	        $this->runSql('DELETE FROM `user_statuses` WHERE `codename` = "approved" LIMIT 1;');
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

} // End Migration1574163640_Remove_Unused_User_Statuses
