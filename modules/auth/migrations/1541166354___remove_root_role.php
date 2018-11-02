<?php

class Migration1541166354_Remove_Root_Role extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1541166354;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_root_role';
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
        if ($this->tableHasColumnValue('roles', 'name', 'root')) {
            // Remove binding to users first
            $this->runSql("DELETE roles_users FROM `roles_users`
LEFT JOIN `roles` ON roles.id = roles_users.role_id
WHERE roles.name = 'root';");

            // Remove role last
            $this->runSql("DELETE FROM `roles` WHERE (`name` = 'root');");
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

} // End Migration1541166354_Remove_Root_Role
