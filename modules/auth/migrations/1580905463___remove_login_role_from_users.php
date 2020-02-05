<?php

class Migration1580905463_Remove_Login_Role_From_Users extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1580905463;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_login_role_from_users';
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
        $this->runSql('DELETE ru FROM `roles_users` AS ru LEFT JOIN `roles` AS r ON r.id = ru.role_id WHERE r.name = "login";');
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1580905463_Remove_Login_Role_From_Users
