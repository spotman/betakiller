<?php

class Migration1544118199_Update_Languages extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1544118199;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Update_languages';
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
        if ($this->tableHasColumn('languages', 'is_system')) {
            $this->runSql("ALTER TABLE `languages` CHANGE `is_system` `is_app` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean marker, 1 means language is used for i18n and other system-wide elements' AFTER `locale`;");
        }

        if (!$this->tableHasColumn('languages', 'is_dev')) {
            $this->runSql("ALTER TABLE `languages` ADD `is_dev` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean marker, 1 means language is used for app and is under development' AFTER `is_app`;");
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

} // End Migration1544118199_Update_Languages
