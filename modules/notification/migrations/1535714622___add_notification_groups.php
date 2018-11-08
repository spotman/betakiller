<?php

class Migration1535714622_Add_Notification_Groups extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1535714622;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_notification_groups';
    }

    /**
     * Returns migration info
     *
     * @return string
     */
    public function description(): string
    {
        return 'Creating a tables for notifications groups';
    }

    /**
     * Takes a migration
     *
     * @return void
     */
    public function up(): void
    {
        $this
            ->createTableMain()
            ->createTableRoles()
            ->createTableUsersOff();
    }

    /**
     * @return $this
     */
    private function createTableMain(): self
    {
        if (!$this->tableExists('notification_groups')) {
            $this->runSql(
                "CREATE TABLE `notification_groups` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `is_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                    `codename` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
                    `description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `codename` (`codename`)
                )
                COLLATE='utf8_unicode_ci'
                ENGINE=InnoDB
                AUTO_INCREMENT=26
                ;"
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function createTableRoles(): self
    {
        if (!$this->tableExists('notification_groups_roles')) {
            $this->runSql(
                "CREATE TABLE `notification_groups_roles` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `group_id` INT(11) UNSIGNED NOT NULL,
                    `role_id` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `group_id_role_id` (`group_id`, `role_id`),
                    INDEX `group_id` (`group_id`),
                    INDEX `role_id` (`role_id`),
                    CONSTRAINT `notifications_groups_roles_group_id_fk` FOREIGN KEY (`group_id`) REFERENCES `notification_groups` (`id`) ON UPDATE CASCADE,
                    CONSTRAINT `notifications_groups_roles_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
                )
                COLLATE='utf8_unicode_ci'
                ENGINE=InnoDB
                AUTO_INCREMENT=34
                ;"
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function createTableUsersOff(): self
    {
        if (!$this->tableExists('notification_groups_users_off')) {
            $this->runSql(
                "CREATE TABLE `notification_groups_users_off` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `group_id` INT(11) UNSIGNED NOT NULL,
                    `user_id` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `group_id_user_id` (`group_id`, `user_id`),
                    INDEX `user_id` (`user_id`),
                    INDEX `group_id` (`group_id`),
                    CONSTRAINT `notifications_groups_users_group_id_fk` FOREIGN KEY (`group_id`) REFERENCES `notification_groups` (`id`) ON UPDATE CASCADE,
                    CONSTRAINT `notifications_groups_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
                )
                COLLATE='utf8_unicode_ci'
                ENGINE=InnoDB
                AUTO_INCREMENT=18
                ;"
            );
        }

        return $this;
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1535714622_Add_Notification_Groups
