<?php

class Migration1583244015_Preset_Stat_Hits_Uuid_And_Unique_Key extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1583244015;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Preset_stat_hits_uuid_and_unique_key';
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
        if ($this->tableHasColumnValue('stat_hits', 'uuid', '')) {
            $this->runSql('UPDATE stat_hits SET uuid = UUID() WHERE uuid = "";');

            $this->runSql('ALTER TABLE `stat_hits` ADD UNIQUE `uuid` (`uuid`);');
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

} // End Migration1583244015_Preset_Stat_Hits_Uuid_And_Unique_Key
