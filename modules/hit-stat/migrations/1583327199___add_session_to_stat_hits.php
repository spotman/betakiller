<?php

class Migration1583327199_Add_Session_To_Stat_Hits extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1583327199;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_session_to_stat_hits';
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
        if (!$this->tableHasColumn('stat_hits', 'session_token')) {
            $this->runSql("ALTER TABLE `stat_hits`
ADD `session_token` varchar(40) COLLATE 'utf8_unicode_ci' NULL AFTER `uuid`,
ADD FOREIGN KEY (`session_token`) REFERENCES `sessions` (`token`) ON DELETE SET NULL;");
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

} // End Migration1583327199_Add_Session_To_Stat_Hits
