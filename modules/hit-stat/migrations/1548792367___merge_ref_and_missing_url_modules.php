<?php

class Migration1548792367_Merge_Ref_And_Missing_Url_Modules extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1548792367;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Merge_ref_and_missing_url_modules';
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
        if (!$this->tableExists('stat_hit_markers')) {
            $this->runSql("CREATE TABLE `stat_hit_markers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `source` varchar(64) NOT NULL,
  `medium` varchar(64) NOT NULL,
  `campaign` varchar(64) NOT NULL,
  `content` varchar(64) NULL,
  `term` varchar(64) NULL
) ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';");
        }

        if ($this->tableExists('ref_pages')) {
            $this->runSql("ALTER TABLE `ref_pages` ADD `is_missing` int(1) unsigned NOT NULL DEFAULT '0' AFTER `uri`, RENAME TO `stat_hit_pages`;");

            $this->runSql('ALTER TABLE `stat_hit_pages`
DROP FOREIGN KEY `stat_hit_pages_ibfk_1`,
ADD FOREIGN KEY (`domain_id`) REFERENCES `stat_hit_domains` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        if ($this->tableExists('ref_links')) {
            $this->runSql('ALTER TABLE `ref_links` RENAME TO `stat_hit_links`;');
        }

        if ($this->tableExists('ref_domains')) {
            $this->runSql('ALTER TABLE `ref_domains` RENAME TO `stat_hit_domains`;');
        }

        if ($this->tableExists('ref_hits')) {
            $this->runSql('TRUNCATE TABLE `ref_hits`');

            $this->runSql('ALTER TABLE `ref_hits`
CHANGE `created_at` `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `id`,
ADD `source_id` int(11) unsigned NULL AFTER `created_at`,
ADD `target_id` int(11) unsigned NOT NULL AFTER `source_id`,
ADD `marker_id` int(11) unsigned NULL AFTER `target_id`,
DROP `source_url`,
DROP `target_url`,
ADD FOREIGN KEY (`source_id`) REFERENCES `stat_hit_pages` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`target_id`) REFERENCES `stat_hit_pages` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`marker_id`) REFERENCES `stat_hit_markers` (`id`) ON DELETE RESTRICT,
RENAME TO `stat_hits`;');
        }

        if ($this->tableExists('redirect_targets')) {
            $this->runSql('ALTER TABLE `redirect_targets` RENAME TO `stat_hit_redirect_targets`;');
        }

        if (!$this->tableHasColumn('stat_hit_pages', 'first_seen_at')) {
            $this->runSql('ALTER TABLE `stat_hit_pages` ADD `first_seen_at` datetime NOT NULL AFTER `uri`;');
        }

        if (!$this->tableHasColumn('stat_hit_pages', 'last_seen_at')) {
            $this->runSql('ALTER TABLE `stat_hit_pages` ADD `last_seen_at` datetime NOT NULL AFTER `first_seen_at`;');
        }

        if (!$this->tableHasColumn('stat_hit_pages', 'redirect_id')) {
            $this->runSql('ALTER TABLE `stat_hit_pages`
ADD `redirect_id` int(11) unsigned NULL AFTER `is_missing`,
ADD FOREIGN KEY (`redirect_id`) REFERENCES `stat_hit_page_redirects` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;');
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

} // End Migration1548792367_Merge_Ref_And_Missing_Url_Modules
