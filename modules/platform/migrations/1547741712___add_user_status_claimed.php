<?php

class Migration1547741712_Add_User_Status_Claimed extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1547741712;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_user_status_claimed';
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
        if (!$this->tableHasColumnValue('user_statuses', 'codename', 'claimed')) {
            $this->runSql("INSERT INTO `user_statuses` (`codename`) VALUES ('claimed');");
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

} // End Migration1547741712_Add_User_Status_Claimed
