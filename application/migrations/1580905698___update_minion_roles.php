<?php

class Migration1580905698_Update_Minion_Roles extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1580905698;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Update_minion_roles';
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
        // Remove all roles
        $this->runSql('DELETE ru FROM `roles_users` AS ru LEFT JOIN `users` AS u ON u.id = ru.user_id WHERE u.username = "minion";');

        // Add "cli" role
        $this->runSql('INSERT INTO `roles` SET `name` = "cli", `description` = "Console task runner"');

        // Bind "cli" role
        $this->runSql('INSERT INTO `roles_users` SET `user_id` = (SELECT id FROM `users` WHERE `username` = "minion"), `role_id` = (SELECT id FROM `roles` WHERE `name` = "cli");');
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1580905698_Update_Minion_Roles
