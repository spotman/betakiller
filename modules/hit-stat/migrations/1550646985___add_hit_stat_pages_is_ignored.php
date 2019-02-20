<?php

class Migration1550646985_Add_Hit_Stat_Pages_Is_Ignored extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1550646985;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_hit_stat_pages_is_ignored';
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
        if (!$this->tableHasColumn('stat_hit_pages', 'is_ignored')) {
            $this->runSql("ALTER TABLE `stat_hit_pages` ADD `is_ignored` int(1) unsigned NOT NULL DEFAULT '0' AFTER `is_missing`;");
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

} // End Migration1550646985_Add_Hit_Stat_Pages_Is_Ignored
