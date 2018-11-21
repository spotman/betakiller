<?php

class Migration1542215555_Move_Fields_From_Ifaces_To_Url_Elements extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1542215555;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Move_fields_from_ifaces_to_url_elements';
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
        if(!$this->tableHasColumn('url_elements', 'is_default')) {
            $this->alterUrlElements();
            $this->migrateData();
            $this->removeUnusedColumns();
        }
	}

    private function alterUrlElements(): void
    {
        $this->runSql("ALTER TABLE `url_elements`
ADD `is_default` int(1) unsigned DEFAULT NULL COMMENT 'Marker for selecting UrlElement when / requested',
ADD `is_dynamic` int(1) unsigned DEFAULT NULL COMMENT 'Boolean marker',
ADD `is_tree` int(1) unsigned DEFAULT NULL COMMENT 'IFace has multi-level tree structure',
ADD `zone_id` int(11) unsigned DEFAULT NULL COMMENT 'Website zone',
ADD `entity_id` int(11) unsigned DEFAULT NULL COMMENT 'Related entity if exists',
ADD `entity_action_id` int(11) unsigned DEFAULT NULL COMMENT 'Related entity action if exists',
ADD UNIQUE KEY `entity_id_entity_action_id_zone_id` (`entity_id`,`entity_action_id`,`zone_id`),
ADD CONSTRAINT `url_elements_ibfk_10` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
ADD CONSTRAINT `url_elements_ibfk_11` FOREIGN KEY (`entity_action_id`) REFERENCES `entity_actions` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
ADD CONSTRAINT `url_elements_ibfk_12` FOREIGN KEY (`zone_id`) REFERENCES `url_element_zones` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT;");
	}

    private function migrateData(): void
    {
        $this->runSql('UPDATE `url_elements` as e LEFT JOIN `ifaces` as i ON e.id = i.element_id
SET e.zone_id = i.zone_id, e.entity_id = i.entity_id, e.entity_action_id = i.entity_action_id, 
e.is_default = i.is_default, e.is_dynamic = i.is_dynamic, e.is_tree = i.is_tree');
    }

    private function removeUnusedColumns(): void
    {
        $this->runSql('ALTER TABLE `ifaces`
DROP FOREIGN KEY `ifaces_ibfk_1`,
DROP FOREIGN KEY `ifaces_ibfk_2`,
DROP FOREIGN KEY `ifaces_ibfk_3`,
DROP INDEX `entity_id_entity_action_id_zone_id`,
DROP INDEX `ifaces_ibfk_2`,
DROP INDEX `ifaces_ibfk_3`;');

        $this->runSql('ALTER TABLE `ifaces`
DROP `is_default`,
DROP `is_dynamic`,
DROP `is_tree`,
DROP `zone_id`,
DROP `entity_id`,
DROP `entity_action_id`;');
    }

    /**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1542215555_Move_Fields_From_Ifaces_To_Url_Elements
