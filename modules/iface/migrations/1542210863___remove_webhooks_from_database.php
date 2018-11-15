<?php

class Migration1542210863_Remove_Webhooks_From_Database extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1542210863;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Remove_webhooks_from_database';
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
        if ($this->tableExists('webhooks')) {
            $this->runSql('DROP TABLE `webhooks`;');
        }

        if ($this->tableExists('webhook_services')) {
            $this->runSql('DROP TABLE `webhook_services`;');
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

} // End Migration1542210863_Remove_Webhooks_From_Database
