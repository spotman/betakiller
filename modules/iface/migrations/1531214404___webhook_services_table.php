<?php

class Migration1531214404_Webhook_Services_Table extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1531214404;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Webhook_services_table';
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
	    if (!$this->tableExists('webhook_services')) {
	        $this->runSql("
CREATE TABLE `webhook_services` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `codename` varchar(16) NOT NULL,
  `label` varchar(64) NOT NULL COMMENT 'Human-readable name'
) ENGINE='InnoDB';
");
	        $this->runSql('
	        ALTER TABLE `webhooks`
CHANGE `service` `service_id` int(11) unsigned NOT NULL AFTER `element_id`,
ADD FOREIGN KEY (`service_id`) REFERENCES `webhook_services` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT;
');
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

} // End Migration1531214404_Webhook_Services_Table
