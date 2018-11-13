<?php

class Migration1541770863_Add_Languages_Is_Default extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1541770863;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_languages_is_default';
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
        if (!$this->tableHasColumn('languages', 'is_default')) {
            $this->runSql("ALTER TABLE `languages` ADD `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean marker';");
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

} // End Migration1541770863_Add_Languages_Is_Default
