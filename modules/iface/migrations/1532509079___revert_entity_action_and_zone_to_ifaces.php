<?php

class Migration1532509079_Revert_Entity_Action_And_Zone_To_Ifaces extends Migration
{
    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1532509079;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Revert_entity_action_and_zone_to_ifaces';
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
        if (!$this->tableHasColumn('ifaces', 'zone_id')) {
            $this->alterIFaceTable();
            $this->migrateDataToIFaces();
            $this->removeUnusedColumns();
        }
    }

    private function alterIFaceTable(): void
    {
        $this->runSql("ALTER TABLE `ifaces`
ADD `label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
ADD `is_default` int(1) unsigned DEFAULT NULL COMMENT 'Marker for selecting IFace when / requested',
ADD `is_dynamic` int(1) unsigned DEFAULT NULL COMMENT 'Boolean marker',
ADD `is_tree` int(1) unsigned DEFAULT NULL COMMENT 'IFace has multi-level tree structure',
ADD `zone_id` int(11) unsigned DEFAULT NULL COMMENT 'Website zone',
ADD `entity_id` int(11) unsigned DEFAULT NULL COMMENT 'Related entity if exists',
ADD `entity_action_id` int(11) unsigned DEFAULT NULL COMMENT 'Related entity action if exists',
ADD UNIQUE KEY `entity_id_entity_action_id_zone_id` (`entity_id`,`entity_action_id`,`zone_id`),
ADD CONSTRAINT `ifaces_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
ADD CONSTRAINT `ifaces_ibfk_2` FOREIGN KEY (`entity_action_id`) REFERENCES `entity_actions` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
ADD CONSTRAINT `ifaces_ibfk_3` FOREIGN KEY (`zone_id`) REFERENCES `url_element_zones` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT;");
    }

    private function migrateDataToIFaces(): void
    {
        $this->runSql('UPDATE `ifaces` as i LEFT JOIN `url_elements` as e ON e.id = i.element_id
SET i.zone_id = e.zone_id, i.entity_id = e.entity_id, i.entity_action_id = e.entity_action_id, 
i.is_default = e.is_default, i.is_dynamic = e.is_dynamic, i.is_tree = e.is_tree, i.label = e.label');
    }

    private function removeUnusedColumns(): void
    {
        $this->runSql('ALTER TABLE `url_elements`
DROP INDEX `entity_id_entity_action_id_zone_id`,
DROP INDEX `entity_action_id`,
DROP INDEX `zone_id`;');

        $this->runSql('ALTER TABLE `url_elements`
DROP `label`,
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

} // End Migration1532509079_Revert_Entity_Action_And_Zone_To_Ifaces
