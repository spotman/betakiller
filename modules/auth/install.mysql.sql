CREATE TABLE IF NOT EXISTS `user_statuses` (
                                 `id` int unsigned NOT NULL AUTO_INCREMENT,
                                 `codename` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'created|approved|verified|locked',
                                 `is_start` tinyint unsigned NOT NULL DEFAULT '0',
                                 `is_finish` tinyint unsigned NOT NULL DEFAULT '0',
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_statuses` (`id`, `codename`, `is_start`, `is_finish`) VALUES
                                                                            (1,	'created',	1,	0),
                                                                            (4,	'blocked',	0,	1),
                                                                            (5,	'confirmed',	0,	0),
                                                                            (7,	'suspended',	0,	0),
                                                                            (8,	'email-changed',	0,	0),
                                                                            (9,	'resumed',	0,	0);
CREATE TABLE IF NOT EXISTS `users` (
                         `id` int unsigned NOT NULL AUTO_INCREMENT,
                         `status_id` int unsigned DEFAULT NULL,
                         `created_at` datetime NOT NULL,
                         `created_from_ip` varchar(46) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Client IP address',
                         `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                         `username` varchar(41) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `first_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `last_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `middle_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `birthday` date DEFAULT NULL,
                         `logins` int NOT NULL DEFAULT '0',
                         `last_login` int DEFAULT NULL,
                         `language_id` int unsigned DEFAULT NULL,
                         `phone` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `is_phone_verified` tinyint unsigned NOT NULL DEFAULT '0',
                         `notify_by_email` tinyint unsigned NOT NULL DEFAULT '1',
                         `is_reg_claimed` tinyint unsigned NOT NULL DEFAULT '0',
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `email` (`email`),
                         UNIQUE KEY `username` (`username`),
                         KEY `language_id` (`language_id`),
                         KEY `status_id` (`status_id`),
                         CONSTRAINT `users_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `user_statuses` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
                         CONSTRAINT `users_ibfk_8` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users and their properties';


CREATE TABLE IF NOT EXISTS `sessions` (
                            `token` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                            `user_id` int unsigned DEFAULT NULL,
                            `created_at` datetime NOT NULL,
                            `last_active_at` datetime NOT NULL,
                            `origin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Origin URL if exists',
                            `is_regenerated` tinyint unsigned NOT NULL DEFAULT '0',
                            `contents` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                            PRIMARY KEY (`token`),
                            KEY `last_active` (`last_active_at`),
                            KEY `user_id` (`user_id`),
                            CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `tokens` (
                          `id` int unsigned NOT NULL AUTO_INCREMENT,
                          `user_id` int unsigned NOT NULL,
                          `value` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                          `created_at` datetime NOT NULL,
                          `ending_at` datetime NOT NULL,
                          `used_at` datetime DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `value` (`value`),
                          KEY `ending_at` (`ending_at`),
                          KEY `tokens_ibfk_1` (`user_id`),
                          CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
                         `id` int unsigned NOT NULL AUTO_INCREMENT,
                         `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                         `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `roles_inheritance` (
                                     `child_id` int unsigned NOT NULL,
                                     `parent_id` int unsigned NOT NULL,
                                     PRIMARY KEY (`child_id`,`parent_id`),
                                     KEY `child_id` (`child_id`),
                                     KEY `parent_id` (`parent_id`),
                                     CONSTRAINT `roles_inheritance_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
                                     CONSTRAINT `roles_inheritance_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `roles_users` (
                               `user_id` int unsigned NOT NULL,
                               `role_id` int unsigned NOT NULL,
                               PRIMARY KEY (`user_id`,`role_id`),
                               KEY `role_id` (`role_id`),
                               CONSTRAINT `roles_users_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
                               CONSTRAINT `roles_users_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
