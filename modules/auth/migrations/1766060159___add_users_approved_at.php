<?php

class Migration1766060159_Add_Users_Approved_At extends \BetaKiller\Migration\AbstractMigration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1766060159;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_users_approved_at';
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
        if ($this->tableExists('users') && !$this->tableHasColumn('users', 'approved_at')) {
            $this->runSql("ALTER TABLE `users` ADD `approved_at` datetime NULL AFTER `login`;");
            $this->runSql("ALTER TABLE `users` ADD INDEX `approved_at` (`approved_at`);");

            $this->runSql('UPDATE `users` AS u
LEFT JOIN `user_statuses` AS s ON (s.id = u.status_id)
SET u.approved_at = u.created_at
WHERE s.codename = "approved"');
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
