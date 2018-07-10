<?php

class Migration1531218342_Webhook_External_Id extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1531218342;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Webhook_external_id';
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
	    if (!$this->tableHasColumn('webhooks', 'external_event_id')) {
	        $this->runSql("
ALTER TABLE `webhooks` ADD `external_event_id` varchar(255) NULL COMMENT 'ID given by external service API';
");
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

} // End Migration1531218342_Webhook_External_Id
