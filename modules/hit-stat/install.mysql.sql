CREATE TABLE `stat_hit_domains` (
                                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                                    `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `is_internal` tinyint unsigned NOT NULL DEFAULT '0',
                                    `is_ignored` tinyint unsigned NOT NULL DEFAULT '0',
                                    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `stat_hit_links` (
                                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                                  `source_id` int unsigned DEFAULT NULL,
                                  `target_id` int unsigned NOT NULL,
                                  `first_seen_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                  `last_seen_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                                  `clicks` int unsigned NOT NULL DEFAULT '0',
                                  PRIMARY KEY (`id`),
                                  UNIQUE KEY `external_id_internal_id_url` (`source_id`,`target_id`),
                                  KEY `external_id` (`source_id`),
                                  KEY `target_id` (`target_id`),
                                  CONSTRAINT `stat_hit_links_ibfk_3` FOREIGN KEY (`source_id`) REFERENCES `stat_hit_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                  CONSTRAINT `stat_hit_links_ibfk_4` FOREIGN KEY (`target_id`) REFERENCES `stat_hit_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `stat_hit_markers` (
                                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                                    `source` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `medium` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `campaign` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `content` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `term` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `stat_hit_page_redirects` (
                                           `id` int unsigned NOT NULL AUTO_INCREMENT,
                                           `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `stat_hit_pages` (
                                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                                  `domain_id` int unsigned NOT NULL,
                                  `uri` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `first_seen_at` datetime NOT NULL,
                                  `last_seen_at` datetime NOT NULL,
                                  `is_missing` int unsigned NOT NULL DEFAULT '0',
                                  `is_ignored` int unsigned NOT NULL DEFAULT '0',
                                  `redirect_id` int unsigned DEFAULT NULL,
                                  `hits` int unsigned NOT NULL DEFAULT '0',
                                  PRIMARY KEY (`id`),
                                  UNIQUE KEY `domain_id_uri` (`domain_id`,`uri`),
                                  KEY `domain_id` (`domain_id`),
                                  KEY `redirect_id` (`redirect_id`),
                                  CONSTRAINT `stat_hit_pages_ibfk_2` FOREIGN KEY (`redirect_id`) REFERENCES `stat_hit_page_redirects` (`id`) ON DELETE SET NULL,
                                  CONSTRAINT `stat_hit_pages_ibfk_3` FOREIGN KEY (`domain_id`) REFERENCES `stat_hit_domains` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `stat_hits` (
                             `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                             `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `session_token` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                             `source_id` int unsigned DEFAULT NULL,
                             `target_id` int unsigned NOT NULL,
                             `marker_id` int unsigned DEFAULT NULL,
                             `user_id` int unsigned DEFAULT NULL,
                             `ip` varchar(46) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `is_processed` tinyint unsigned NOT NULL DEFAULT '0',
                             `is_protected` tinyint unsigned NOT NULL DEFAULT '0',
                             PRIMARY KEY (`id`),
                             UNIQUE KEY `uuid` (`uuid`),
                             KEY `source_id` (`source_id`),
                             KEY `target_id` (`target_id`),
                             KEY `marker_id` (`marker_id`),
                             KEY `user_id` (`user_id`),
                             KEY `session_token` (`session_token`),
                             CONSTRAINT `stat_hits_ibfk_1` FOREIGN KEY (`source_id`) REFERENCES `stat_hit_pages` (`id`) ON DELETE CASCADE,
                             CONSTRAINT `stat_hits_ibfk_2` FOREIGN KEY (`target_id`) REFERENCES `stat_hit_pages` (`id`) ON DELETE CASCADE,
                             CONSTRAINT `stat_hits_ibfk_3` FOREIGN KEY (`marker_id`) REFERENCES `stat_hit_markers` (`id`),
                             CONSTRAINT `stat_hits_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
