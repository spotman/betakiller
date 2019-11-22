<?php

class Migration1574092505_Increase_Url_Element_Zones_Codename extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1574092505;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Increase_url_element_zones_codename';
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
        $this->runSql("ALTER TABLE `url_element_zones`
CHANGE `name` `name` varchar(32) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
COLLATE 'utf8mb4_unicode_ci';");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1574092505_Increase_Url_Element_Zones_Codename
