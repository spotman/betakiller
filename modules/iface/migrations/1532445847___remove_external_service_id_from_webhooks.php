<?php

class Migration1532445847_Remove_External_Service_Id_From_Webhooks extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1532445847;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_external_service_id_from_webhooks';
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
        if ($this->tableHasColumn('webhooks', 'external_event_id')) {
            $this->runSql('ALTER TABLE `webhooks` CHANGE `description` `description` varchar(255) NULL AFTER `event`, DROP `external_event_id`;');
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

} // End Migration1532445847_Remove_External_Service_Id_From_Webhooks
