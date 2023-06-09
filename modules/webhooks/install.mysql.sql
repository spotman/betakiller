CREATE TABLE IF NOT EXISTS `webhook_log` (
                               `id` int unsigned NOT NULL AUTO_INCREMENT,
                               `codename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               `created_at` datetime NOT NULL,
                               `status` tinyint unsigned NOT NULL,
                               `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                               `request_data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                               PRIMARY KEY (`id`),
                               KEY `codename` (`codename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
