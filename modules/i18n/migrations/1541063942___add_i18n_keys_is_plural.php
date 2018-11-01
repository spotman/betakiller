<?php

class Migration1541063942_Add_I18n_Keys_Is_Plural extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1541063942;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_i18n_keys_is_plural';
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
        if (!$this->tableHasColumn('i18n_keys', 'is_plural')) {
            $this->runSql('ALTER TABLE `i18n_keys` ADD `is_plural` tinyint(1) unsigned NOT NULL;');
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

} // End Migration1541063942_Add_I18n_Keys_Is_Plural
