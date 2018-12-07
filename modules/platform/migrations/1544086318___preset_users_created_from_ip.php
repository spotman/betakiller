<?php

class Migration1544086318_Preset_Users_Created_From_Ip extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1544086318;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Preset_users_created_from_ip';
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
        $this->runSql("UPDATE `users` SET `created_from_ip` = '8.8.8.8' WHERE LENGTH(`created_from_ip`) = 0;");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1544086318_Preset_Users_Created_From_Ip
