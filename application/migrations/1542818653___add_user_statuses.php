<?php

class Migration1542818653_Add_User_Statuses extends Migration
{
    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1542818653;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_user_statuses';
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
        if (!$this->tableExists('user_statuses')) {
            $this->runSql("CREATE TABLE `user_statuses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `codename` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'created|approved|verified|locked|confirmed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

            $this->runSql("INSERT INTO `user_statuses` (`id`, `codename`) VALUES
(1,	'created'),
(2,	'approved'),
(3,	'verified'),
(4,	'blocked'),
(5,	'confirmed');");
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

} // End Migration1542818653_Add_Account_Status_Confirmed
