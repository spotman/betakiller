<?php

class Migration1611131888_Add_Users_Is_Reg_Claimed extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1611131888;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_users_is_reg_claimed';
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
        if (!$this->tableHasColumn('users', 'is_reg_claimed')) {
            $this->runSql("ALTER TABLE `users` ADD `is_reg_claimed` tinyint unsigned NOT NULL DEFAULT '0';");

            // Migrate data to new marker
            $this->runSql('UPDATE `users` AS u
LEFT JOIN `user_statuses` AS us ON us.id = u.status_id
SET u.is_reg_claimed = 1
WHERE us.codename = "claimed";');

            // Cleanup
            $this->runSql('UPDATE `users` AS u
LEFT JOIN `user_statuses` AS us ON us.id = u.status_id

SET u.status_id = (SELECT id FROM `user_statuses` WHERE codename = "confirmed")
WHERE us.codename = "claimed";');
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

} // End Migration1611131888_Add_Users_Is_Reg_Claimed
