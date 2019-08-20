<?php

class Migration1566287914_Allow_Empty_Origin_In_Sessions extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1566287914;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Allow_empty_origin_in_sessions';
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
        $this->runSql("ALTER TABLE `sessions` CHANGE `origin` `origin` varchar(255) COLLATE 'utf8_unicode_ci' NULL COMMENT 'Origin URL if exists' AFTER `last_active_at`;");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1566287914_Allow_Empty_Origin_In_Sessions
