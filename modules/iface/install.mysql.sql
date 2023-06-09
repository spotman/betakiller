CREATE TABLE IF NOT EXISTS `url_element_types` (
                                     `id` int unsigned NOT NULL AUTO_INCREMENT,
                                     `codename` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `url_element_types` (`id`, `codename`) VALUES
                                                       (1,	'IFace'),
                                                       (2,	'WebHook'),
                                                       (3,	'Action');

CREATE TABLE IF NOT EXISTS `url_element_zones` (
                                     `id` int unsigned NOT NULL AUTO_INCREMENT,
                                     `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `url_element_zones` (`id`, `name`) VALUES
                                                   (1,	'public'),
                                                   (2,	'admin'),
                                                   (3,	'personal'),
                                                   (4,	'developer'),
                                                   (5,	'preview');

CREATE TABLE `url_element_acl_rules` (
                                         `id` int unsigned NOT NULL AUTO_INCREMENT,
                                         `element_id` int unsigned NOT NULL,
                                         `resource_id` int unsigned NOT NULL,
                                         `permission_id` int unsigned NOT NULL,
                                         PRIMARY KEY (`id`),
                                         UNIQUE KEY `iface_id_resource_id_permission_id` (`element_id`,`resource_id`,`permission_id`),
                                         KEY `resource_id` (`resource_id`),
                                         KEY `permission_id` (`permission_id`),
                                         CONSTRAINT `url_element_acl_rules_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `acl_resources` (`id`) ON UPDATE CASCADE,
                                         CONSTRAINT `url_element_acl_rules_ibfk_3` FOREIGN KEY (`permission_id`) REFERENCES `acl_resource_permissions` (`id`) ON UPDATE CASCADE,
                                         CONSTRAINT `url_element_acl_rules_ibfk_5` FOREIGN KEY (`element_id`) REFERENCES `url_elements` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `layouts` (
                           `id` int NOT NULL AUTO_INCREMENT,
                           `codename` varchar(32) CHARACTER SET utf8 COLLATE utf8mb4_unicode_ci NOT NULL,
                           `title` varchar(32) CHARACTER SET utf8 COLLATE utf8mb4_unicode_ci NOT NULL,
                           `is_default` tinyint unsigned DEFAULT NULL,
                           PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `entities` (
                            `id` int unsigned NOT NULL AUTO_INCREMENT,
                            `slug` varchar(16) CHARACTER SET utf8 COLLATE utf8mb4_unicode_ci NOT NULL,
                            `model_name` varchar(32) CHARACTER SET utf8 COLLATE utf8mb4_unicode_ci NOT NULL,
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `entities` (`id`, `slug`, `model_name`) VALUES
                                                        (1,	'post',	'ContentPost'),
                                                        (2,	'category',	'ContentCategory');

DROP TABLE IF EXISTS `entity_actions`;
CREATE TABLE `entity_actions` (
                                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                                  `name` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `entity_actions` (`id`, `name`) VALUES
                                                (1,	'list'),
                                                (2,	'read'),
                                                (3,	'update'),
                                                (4,	'create'),
                                                (5,	'delete'),
                                                (6,	'search');
