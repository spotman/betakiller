<?php

class Migration1542109749_Add_Tokens_Table extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1542109749;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_tokens_table';
    }

    /**
     * Returns migration info
     *
     * @return string
     */
    public function description(): string
    {
        return 'Add table for tokens.';
    }

    /**
     * Takes a migration
     *
     * @return void
     */
    public function up(): void
    {
        if (!$this->tableExists('tokens')) {
            $this->runSql('
	            CREATE TABLE `tokens` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` INT(11) UNSIGNED NOT NULL,
                    `value` CHAR(64) NOT NULL COLLATE "utf8_unicode_ci",
                    `ending_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `value` (`value`),
                    INDEX `ending_at` (`ending_at`),
                    CONSTRAINT `tokens_ibfk_1` 
                      FOREIGN KEY (`user_id`) 
                      REFERENCES `users` (`id`) 
                      ON UPDATE CASCADE 
                      ON DELETE CASCADE
                )
                COLLATE="utf8_unicode_ci"
                ENGINE=InnoDB;
	        ');
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

} // End Migration1542109749_Add_Tokens_Table
