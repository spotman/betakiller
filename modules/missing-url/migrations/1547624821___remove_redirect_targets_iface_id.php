<?php

class Migration1547624821_Remove_Redirect_Targets_Iface_Id extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1547624821;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_redirect_targets_iface_id';
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
        if ($this->tableHasColumn('redirect_targets', 'iface_id')) {
            $this->runSql('ALTER TABLE `redirect_targets` DROP FOREIGN KEY `redirect_targets_ibfk_2`, DROP `iface_id`;');
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

} // End Migration1547624821_Remove_Redirect_Targets_Iface_Id
