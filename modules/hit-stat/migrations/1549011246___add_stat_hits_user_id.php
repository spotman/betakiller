<?php

class Migration1549011246_Add_Stat_Hits_User_Id extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1549011246;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_stat_hits_user_id';
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
        if (!$this->tableHasColumn('stat_hits', 'user_id')) {
            $this->runSql('ALTER TABLE `stat_hits`
ADD `user_id` int(11) unsigned NULL AFTER `marker_id`,
ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;');
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

} // End Migration1549011246_Add_Stat_Hits_User_Id
