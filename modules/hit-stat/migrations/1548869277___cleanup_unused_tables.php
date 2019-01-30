<?php

class Migration1548869277_Cleanup_Unused_Tables extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1548869277;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Cleanup_unused_tables';
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
        if ($this->tableExists('missing_urls_missing_url_referrers')) {
            $this->runSql('DROP TABLE `missing_urls_missing_url_referrers`;');
        }

        if ($this->tableExists('missing_urls')) {
            $this->runSql('DROP TABLE `missing_urls`;');
        }

        if ($this->tableExists('missing_url_referrers')) {
            $this->runSql('DROP TABLE `missing_url_referrers`;');
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

} // End Migration1548869277_Cleanup_Unused_Tables
