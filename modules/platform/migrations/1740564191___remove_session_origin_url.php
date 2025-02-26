<?php
class Migration1740564191_Remove_Session_Origin_Url extends \BetaKiller\Migration\AbstractMigration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1740564191;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Remove_session_origin_url';
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
        if ($this->tableHasColumn('sessions', 'origin')) {
            $this->runSql("ALTER TABLE `sessions` DROP `origin`;");
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
}
