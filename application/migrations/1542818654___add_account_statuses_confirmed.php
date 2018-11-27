<?php

class Migration1542818654_Add_Account_Statuses_Confirmed extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1542818654;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_account_statuses_confirmed';
    }

    /**
     * Returns migration info
     *
     * @return string
     */
    public function description(): string
    {
        return 'Adding status "confirmed" to table "account_statuses".';
    }

    /**
     * Takes a migration
     *
     * @return void
     */
    public function up(): void
    {
        $value  = \BetaKiller\Model\UserStatus::STATUS_CONFIRMED;
        $exists = $this->tableHasColumnValue('account_statuses', 'codename', $value);
        if (!$exists) {
            $this->runSql("INSERT INTO `account_statuses` (`codename`) VALUES ('$value');");
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
