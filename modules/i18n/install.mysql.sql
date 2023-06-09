CREATE TABLE IF NOT EXISTS `i18n_keys` (
                             `id` int unsigned NOT NULL AUTO_INCREMENT,
                             `codename` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `is_plural` tinyint unsigned NOT NULL,
                             `i18n` json NOT NULL COMMENT 'Localization values',
                             PRIMARY KEY (`id`),
                             UNIQUE KEY `key` (`codename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Translation keys';

CREATE TABLE IF NOT EXISTS `languages` (
                             `id` int unsigned NOT NULL AUTO_INCREMENT,
                             `iso_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Lang ISO 639-1 code',
                             `locale` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Locale string',
                             `is_app` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean marker, 1 means language is used for i18n and other system-wide elements',
                             `is_dev` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean marker, 1 means language is used for app and is under development',
                             `is_default` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean marker',
                             `i18n` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `languages` (`id`, `iso_code`, `locale`, `is_app`, `is_dev`, `is_default`, `i18n`) VALUES
    (1,	'ru',	'ru_RU',	1,	0,	1,	'{\"en\":\"Russian\",\"fr\":\"russe\",\"de\":\"Russisch\"}');
