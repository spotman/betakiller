<?php

class Migration1541007702_Add_I18n_Tables extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1541007702;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_i18n_tables';
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
        if (!$this->tableExists('i18n_keys')) {
            $this->runSql("CREATE TABLE `i18n_keys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Translation keys'");
        }

        if (!$this->tableExists('i18n_values')) {
            $this->runSql("CREATE TABLE `i18n_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key_id` int(11) unsigned NOT NULL,
  `language_id` int(11) unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key_id` (`key_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `i18n_values_ibfk_1` FOREIGN KEY (`key_id`) REFERENCES `i18n_keys` (`id`),
  CONSTRAINT `i18n_values_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Translation strings'");
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

} // End Migration1541007702_Add_I18n_Tables
