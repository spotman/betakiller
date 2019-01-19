<?php

class Migration1542818655_Add_User_Status_Id extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1542818655;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_user_status_id';
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
        if (!$this->tableHasColumn('users', 'status_id')) {
            $this->runSql('ALTER TABLE `users`
  ADD `status_id` int(11) unsigned DEFAULT NULL after `id`,
  ADD KEY `status_id` (`status_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `user_statuses` (`id`) ON UPDATE CASCADE;');
        }

        if (!$this->tableHasColumn('users', 'created_at')) {
            $this->runSql('ALTER TABLE `users` ADD `created_at` datetime NOT NULL AFTER `status_id`;');
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

} // End Migration1542818654_Add_Account_Status_Confirmed
