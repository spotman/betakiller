<?php

class Migration1538120598_Add_Users_Session_Id extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1538120598;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Add_users_session_id';
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
	    if (!$this->tableHasColumn('users', 'session_id')) {
	        $this->runSql("ALTER TABLE `users`
ADD `session_id` varchar(24) COLLATE 'utf8_unicode_ci' NULL AFTER `last_login`,
ADD FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`);");
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

} // End Migration1538120598_Add_Users_Session_Id
