<?php

class Migration1586760784_Create_Virtual_I18n_Columns extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1586760784;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Create_virtual_i18n_columns';
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
        $this->runSql("ALTER TABLE `i18n_keys` CHANGE `i18n` `i18n` json NOT NULL COMMENT 'Localization values' AFTER `is_plural`;");

        $this->runSql("ALTER TABLE `i18n_keys`
ADD COLUMN `en` varchar(3000) GENERATED ALWAYS AS (`i18n` ->> '$.en') NULL,
ADD COLUMN `de` varchar(3000) GENERATED ALWAYS AS (`i18n` ->> '$.de') NULL AFTER `en`;");

        $this->runSql('ALTER TABLE `i18n_keys` DROP `en`, DROP `de`;');
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1586760784_Create_Virtual_I18n_Columns
