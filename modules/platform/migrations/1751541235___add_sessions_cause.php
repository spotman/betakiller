<?php

class Migration1751541235_Add_Sessions_Cause extends \BetaKiller\Migration\AbstractMigration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1751541235;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_sessions_cause';
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
        if (!$this->tableHasColumn('sessions', 'cause')) {
            $this->runSql("ALTER TABLE `sessions` ADD `cause` varchar(16) COLLATE 'utf8_unicode_ci' NULL AFTER `token`;");
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
}
