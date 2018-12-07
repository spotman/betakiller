<?php

class Migration1542888918_Add_Users_Created_From_Ip extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1542888918;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_users_created_from_ip';
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
	    if (!$this->tableHasColumn('users', 'created_from_ip')) {
	        $this->runSql("ALTER TABLE `users` ADD `created_from_ip` varchar(46) NOT NULL COMMENT 'Client IP address' AFTER `created_at`;");
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

} // End Migration1542888918_Add_Users_Created_From_Ip
