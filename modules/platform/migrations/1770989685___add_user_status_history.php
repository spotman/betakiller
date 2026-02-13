<?php

class Migration1770989685_Add_User_Status_History extends \BetaKiller\Migration\AbstractMigration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1770989685;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_user_status_history';
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
        if (!$this->tableExists('user_status_history')) {
            $this->runSql(
                "CREATE TABLE `user_status_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` datetime NOT NULL,
  `created_by` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `status_id` int unsigned NOT NULL,
  `transition` varchar(32) NOT NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users_list` (`user_id`) ON DELETE RESTRICT,
  FOREIGN KEY (`user_id`) REFERENCES `users_list` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`status_id`) REFERENCES `user_statuses` (`id`) ON DELETE CASCADE
) ENGINE='InnoDB' COLLATE 'utf8mb4_unicode_ci';"
            );
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
