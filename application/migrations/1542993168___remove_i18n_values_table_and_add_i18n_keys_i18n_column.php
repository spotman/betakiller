<?php

class Migration1542993168_Remove_I18n_Values_Table_And_Add_I18n_Keys_I18n_Column extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1542993168;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_i18n_values_table_and_add_i18n_keys_i18n_column';
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
        if ($this->tableExists('i18n_values')) {
            $this->runSql('DROP TABLE `i18n_values`;');
            $this->runSql("ALTER TABLE `i18n_keys` ADD `i18n` JSON NOT NULL COMMENT 'Localization values';");
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

} // End Migration1542993167_Remove_I18n_Tables_And_Add_I18n_Columns
