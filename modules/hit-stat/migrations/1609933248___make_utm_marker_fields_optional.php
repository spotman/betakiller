<?php

class Migration1609933248_Make_Utm_Marker_Fields_Optional extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1609933248;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Make_utm_marker_fields_optional';
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
	    // Change collation to utf8mbf also
	    $this->runSql("ALTER TABLE `stat_hit_markers`
CHANGE `source` `source` varchar(64) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `id`,
CHANGE `medium` `medium` varchar(64) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `source`,
CHANGE `campaign` `campaign` varchar(64) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `medium`,
CHANGE `content` `content` varchar(64) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `campaign`,
CHANGE `term` `term` varchar(64) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `content`,
COLLATE 'utf8mb4_unicode_ci';");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1609933248_Make_Utm_Marker_Fields_Optional
