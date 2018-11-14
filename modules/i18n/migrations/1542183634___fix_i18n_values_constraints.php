<?php

class Migration1542183634_Fix_I18n_Values_Constraints extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1542183634;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Fix_i18n_values_constraints';
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
        if ($this->hasTableIndex('i18n_values', 'i18n_values_ibfk_1')) {
            $this->dropTableIndexForeign('i18n_values', 'i18n_values_ibfk_1');
            $this->runSql('ALTER TABLE `i18n_values` ADD FOREIGN KEY (`key_id`) REFERENCES `i18n_keys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        if ($this->hasTableIndex('i18n_values', 'i18n_values_ibfk_2')) {
            $this->dropTableIndexForeign('i18n_values', 'i18n_values_ibfk_2');
            $this->runSql('ALTER TABLE `i18n_values` ADD FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
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

} // End Migration1542183634_Fix_I18n_Values_Constraints
