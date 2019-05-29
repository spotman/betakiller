<?php

class Migration1559135628_Add_Notification_Frequencies extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1559135628;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_notification_frequencies';
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
        if (!$this->tableExists('notification_frequencies')) {

            $this->runSql('CREATE TABLE `notification_frequencies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `codename` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cron_expression` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
');

            $this->runSql("INSERT INTO `notification_frequencies` (`id`, `codename`, `cron_expression`) VALUES
(1,	'immediately',	NULL),
(2,	'oaw',	'0 9 * * MON'),
(3,	'bw',	'0 9 * * TUE,THU'),
(4,	'tiw',	'0 9 * * MON,WED,FRI');");
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

} // End Migration1559135628_Add_Notification_Frequencies
