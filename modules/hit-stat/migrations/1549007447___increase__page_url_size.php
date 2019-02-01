<?php

class Migration1549007447_Increase__Page_Url_Size extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1549007447;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Increase__page_url_size';
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
        $this->runSql("ALTER TABLE `stat_hit_pages` CHANGE `uri` `uri` varchar(512) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `domain_id`;");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1549007447_Increase__Page_Url_Size
