<?php

class Migration1591615089_Add_Sessions_Is_Regenerated extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1591615089;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_sessions_is_regenerated';
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
        if (!$this->tableHasColumn('sessions', 'is_regenerated')) {
            $this->runSql("ALTER TABLE `sessions` ADD `is_regenerated` tinyint unsigned NOT NULL DEFAULT '0' AFTER `origin`;");
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

} // End Migration1591615089_Add_Sessions_Is_Regenerated
