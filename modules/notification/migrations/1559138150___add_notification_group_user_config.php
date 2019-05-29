<?php

class Migration1559138150_Add_Notification_Group_User_Config extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1559138150;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_notification_group_user_config';
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
        if (!$this->tableExists('notification_group_user_config')) {
            $this->runSql("CREATE TABLE `notification_group_user_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `freq_id` int(11) unsigned NOT NULL,
  UNIQUE `user_id_group_id` (`user_id`, `group_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`group_id`) REFERENCES `notification_groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`freq_id`) REFERENCES `notification_frequencies` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE='InnoDB' COLLATE 'utf8mb4_unicode_ci';");
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

} // End Migration1559138150_Add_Notification_Group_User_Config
