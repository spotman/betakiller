<?php

class Migration1559132424_Add_Enqueue extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1559132424;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_enqueue';
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
        if (!$this->tableExists('enqueue')) {
            $this->runSql("CREATE TABLE `enqueue` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `published_at` bigint(20) NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `headers` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `properties` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `redelivered` tinyint(1) DEFAULT NULL,
  `queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `priority` smallint(6) DEFAULT NULL,
  `delayed_until` bigint(20) DEFAULT NULL,
  `time_to_live` bigint(20) DEFAULT NULL,
  `delivery_id` char(36) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:guid)',
  `redeliver_after` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CFC35A6862A6DC27E0D4FDE17FFD7F63121369211A065DF8BF396750` (`priority`,`published_at`,`queue`,`delivery_id`,`delayed_until`,`id`),
  KEY `IDX_CFC35A68AA0BDFF712136921` (`redeliver_after`,`delivery_id`),
  KEY `IDX_CFC35A68E0669C0612136921` (`time_to_live`,`delivery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
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

} // End Migration1559132424_Add_Enqueue
