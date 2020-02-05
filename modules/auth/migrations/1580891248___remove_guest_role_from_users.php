<?php

class Migration1580891248_Remove_Guest_Role_From_Users extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1580891248;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_guest_role_from_users';
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
        $this->runSql('DELETE ru FROM `roles_users` AS ru LEFT JOIN `roles` AS r ON r.id = ru.role_id WHERE r.name = "guest";');
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1580891248_Remove_Guest_Role_From_Users
