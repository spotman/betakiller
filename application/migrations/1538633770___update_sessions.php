<?php

class Migration1538633770_Update_Sessions extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1538633770;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Update_sessions';
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
        if ($this->tableExists('user_tokens')) {
            if ($this->tableHasColumn('users', 'session_id')) {
                // Remove users.session_id
                $this->runSql('ALTER TABLE `users` DROP FOREIGN KEY `users_ibfk_11`, DROP `session_id`;');
            }

            // Remove all records
            $this->runSql('DELETE FROM `sessions`;');

            // Update sessions
            $this->runSql("ALTER TABLE `sessions`
DROP INDEX `PRIMARY`,
CHANGE `session_id` `token` varchar(40) COLLATE 'utf8_unicode_ci' NOT NULL,
ADD `user_id` int(11) unsigned NULL AFTER `token`,
ADD `created_at` datetime NOT NULL AFTER `user_id`,
CHANGE `last_active` `last_active_at` datetime NOT NULL AFTER `created_at`,
ADD PRIMARY KEY `token` (`token`),
ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;");

            // Remove user_tokens
            $this->runSql('DROP TABLE `user_tokens`;');
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

} // End Migration1538633770_Update_Sessions
