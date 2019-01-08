<?php

class Migration1546857810_Add_User_Tokens_Created_At_And_Used_At extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1546857810;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_user_tokens_created_at_and_used_at';
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
        if (!$this->tableHasColumn('tokens', 'created_at')) {
            $this->runSql('ALTER TABLE `tokens` ADD `created_at` datetime NOT NULL AFTER `value`, ADD `used_at` datetime NULL;');
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

} // End Migration1546857810_Add_User_Tokens_Created_At_And_Used_At
