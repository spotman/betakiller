<?php

class Migration1544104106_Remove_Languages_Label_And_Add_I18n extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1544104106;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_languages_label_and_add_i18n';
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
        if ($this->tableHasColumn('languages', 'label')) {
            $this->runSql('ALTER TABLE `languages` DROP `label`;');
        }

        if ($this->tableHasColumn('languages', 'name')) {
            $this->runSql("ALTER TABLE `languages` CHANGE `name` `iso_code` varchar(2) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Lang ISO 639-1 code' AFTER `id`;");
        }

        if (!$this->tableHasColumn('languages', 'i18n')) {
            $this->runSql('ALTER TABLE `languages` ADD `i18n` json NOT NULL;');
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

} // End Migration1544104106_Remove_Languages_Label_And_Add_I18n
