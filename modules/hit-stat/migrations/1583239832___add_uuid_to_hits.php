<?php

class Migration1583239832_Add_Uuid_To_Hits extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1583239832;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_uuid_to_hits';
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
        if (!$this->tableHasColumn('stat_hits', 'uuid')) {
            $this->runSql("ALTER TABLE `stat_hits`
ADD `uuid` char(36) NOT NULL AFTER `id`,
COLLATE 'utf8mb4_unicode_ci';");
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

} // End Migration1583239832_Add_Uuid_To_Hits
