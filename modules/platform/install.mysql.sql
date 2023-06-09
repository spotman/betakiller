CREATE TABLE IF NOT EXISTS `acl_resource_permissions` (
                                            `id` int unsigned NOT NULL AUTO_INCREMENT,
                                            `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `acl_resources` (
                                 `id` int unsigned NOT NULL AUTO_INCREMENT,
                                 `parent_id` int unsigned DEFAULT NULL,
                                 `codename` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `parent_id` (`parent_id`),
                                 CONSTRAINT `acl_resources_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `acl_resources` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `acl_rules` (
                             `id` int unsigned NOT NULL AUTO_INCREMENT,
                             `role_id` int unsigned DEFAULT NULL,
                             `resource_id` int unsigned DEFAULT NULL COMMENT 'Resource to grant permission to',
                             `permission_id` int unsigned DEFAULT NULL COMMENT 'Action to grant permission to',
                             `is_allowed` tinyint(1) DEFAULT NULL,
                             PRIMARY KEY (`id`),
                             KEY `resource_id` (`resource_id`),
                             KEY `permission_id` (`permission_id`),
                             KEY `role_id` (`role_id`),
                             CONSTRAINT `acl_rules_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `acl_resources` (`id`) ON UPDATE CASCADE,
                             CONSTRAINT `acl_rules_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `acl_resource_permissions` (`id`) ON UPDATE CASCADE,
                             CONSTRAINT `acl_rules_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
