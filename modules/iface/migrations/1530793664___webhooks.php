<?php

class Migration1530793664_Webhooks extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1530793664;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Webhooks';
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
        if (!$this->table_exists('url_element_types')) {
            $this->createUrlElementTypesTable();
            $this->fillUrlElementTypesTable();
        }

        if (!$this->table_exists('url_elements')) {
	        $this->createUrlElementsTable();
	        $this->migrateDataFromIFaceTable();
	        $this->updateIFaceTable();
        }

        if (!$this->table_exists('url_element_acl_rules')) {
            $this->updateIFaceAclRules();
        }

        if (!$this->table_exists('url_element_zones')) {
            $this->updateIFaceZones();
        }

        if (!$this->table_exists('webhooks')) {
            $this->createWebHooksTable();
        }
    }

	private function createUrlElementTypesTable(): void
    {
        $this->run_sql('
CREATE TABLE `url_element_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `codename` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
');
    }

    private function fillUrlElementTypesTable(): void
    {
        $this->run_sql("INSERT INTO `url_element_types` (`id`, `codename`) VALUES (1, 'IFace'), (2, 'WebHook');");
    }

	private function createUrlElementsTable(): void
    {
        $this->run_sql("
CREATE TABLE `url_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL means root element',
  `type_id` int(11) unsigned NOT NULL,
  `is_default` tinyint(1) unsigned DEFAULT NULL COMMENT 'Marker for selecting IFace when / requested',
  `is_dynamic` int(1) unsigned DEFAULT NULL COMMENT 'Boolean marker',
  `is_tree` int(1) unsigned DEFAULT NULL COMMENT 'IFace has multi-level tree structure',
  `codename` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `zone_id` int(11) unsigned NOT NULL COMMENT 'Website zone',
  `entity_id` int(11) unsigned DEFAULT NULL COMMENT 'Related entity if exists',
  `entity_action_id` int(11) unsigned DEFAULT NULL COMMENT 'Related entity action if exists',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codename` (`codename`),
  UNIQUE KEY `parent_id` (`parent_id`,`uri`),
  UNIQUE KEY `entity_id_entity_action_id_zone_id` (`entity_id`,`entity_action_id`,`zone_id`),
  KEY `entity_action_id` (`entity_action_id`),
  KEY `zone_id` (`zone_id`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `url_elements_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `url_element_types` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `url_elements_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `url_elements` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='URL elements tree'
");
    }

	private function migrateDataFromIFaceTable(): void
    {
        $this->run_sql('
INSERT INTO `url_elements` (`id`, `parent_id`, `type_id`, `label`, `is_default`, `is_dynamic`, `is_tree`, `codename`, `uri`, `zone_id`, `entity_id`, `entity_action_id`)
SELECT `id`, `parent_id`, 1, `label`, `is_default`, `is_dynamic`, `is_tree`, `codename`, `uri`, `zone_id`, `entity_id`, `entity_action_id`
FROM `ifaces`
');
    }

	private function updateIFaceTable(): void
    {
        // Add `type_id`
        $this->run_sql('
ALTER TABLE `ifaces`
ADD `element_id` int(11) unsigned NULL AFTER `id`;
');
        // Set `url_element_id` = `id`
        $this->run_sql('UPDATE `ifaces` SET `element_id` = `id`;');

        // Make url_element_id NOT NULL and add constraints
        $this->run_sql('
ALTER TABLE `ifaces`
CHANGE `element_id` `element_id` int(11) unsigned NOT NULL AFTER `id`,
ADD FOREIGN KEY (`element_id`) REFERENCES `url_elements` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
');

        // Add unique index for `element_id`
        $this->run_sql('
ALTER TABLE `ifaces`
ADD UNIQUE `element_id` (`element_id`),
DROP INDEX `element_id`;
');
    }

    private function updateIFaceAclRules(): void
    {
        // Rename
        $this->run_sql('ALTER TABLE `iface_acl_rules` RENAME TO `url_element_acl_rules`;');

        // Drop FK
        $this->run_sql('ALTER TABLE `url_element_acl_rules` DROP FOREIGN KEY `url_element_acl_rules_ibfk_1`');

        // Rename column and add FK
        $this->run_sql('
ALTER TABLE `url_element_acl_rules`
CHANGE `iface_id` `element_id` int(11) unsigned NOT NULL AFTER `id`,
ADD FOREIGN KEY (`element_id`) REFERENCES `url_elements` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
');
    }

    private function updateIFaceZones(): void
    {
        $this->run_sql('ALTER TABLE `iface_zones` RENAME TO `url_element_zones`');
    }

    private function createWebHooksTable(): void
    {
        $this->run_sql('
CREATE TABLE `webhooks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(11) unsigned NOT NULL,
  `service` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `event` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `description` int(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `element_id` (`element_id`),
  CONSTRAINT `webhooks_ibfk_2` FOREIGN KEY (`element_id`) REFERENCES `url_elements` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');
    }

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1530793664_Webhooks
