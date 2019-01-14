<?php

class Migration1547461711_Add_Sessions_Origin extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1547461711;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_sessions_origin';
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
        if (!$this->tableHasColumn('sessions', 'origin')) {
            $this->runSql("ALTER TABLE `sessions` ADD `origin` varchar(255) NOT NULL COMMENT 'Origin URL' AFTER `last_active_at`;");
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

} // End Migration1547461711_Add_Sessions_Origin
