<?php

class Migration1586944713_Create_Content_Tables extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1586944713;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Create_content_tables';
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
        $this->runSql("CREATE TABLE IF NOT EXISTS `content_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL,
  `is_active` int unsigned NOT NULL DEFAULT '1',
  `place` int unsigned NOT NULL DEFAULT '0',
  `uri` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `wp_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `content_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `content_categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_post_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql("INSERT INTO `content_post_types` (`id`, `name`) VALUES
(1,	'article'),
(2,	'page');");

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_post_statuses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `codename` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `is_start` int unsigned DEFAULT NULL,
  `is_finish` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_posts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` int unsigned NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int unsigned NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_id` int unsigned DEFAULT NULL,
  `uri` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `revision_id` int unsigned DEFAULT NULL,
  `views_count` int unsigned NOT NULL,
  `wp_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `revision_id` (`revision_id`),
  KEY `category_id` (`category_id`),
  KEY `type` (`type`),
  KEY `status_id` (`status_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `content_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `content_categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_posts_ibfk_2` FOREIGN KEY (`type`) REFERENCES `content_post_types` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_posts_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `content_post_statuses` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_posts_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_post_revisions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int unsigned NOT NULL,
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `created_at` (`created_at`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `content_post_revisions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `content_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `content_post_revisions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql('ALTER TABLE `content_posts` ADD CONSTRAINT `content_posts_ibfk_5` FOREIGN KEY (`revision_id`) REFERENCES `content_post_revisions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;');

        $this->runSql("CREATE TABLE IF NOT EXISTS `content_post_thumbnails` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_post_id` int unsigned DEFAULT NULL,
  `original_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `uploaded_by` int unsigned NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_modified_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `width` int unsigned NOT NULL,
  `height` int unsigned NOT NULL,
  `size` int NOT NULL,
  `mime` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(41) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'sha-1 40 hex chars + 1 vor varchar type',
  `alt` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `wp_id` bigint DEFAULT NULL,
  `wp_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `place` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `content_post_id` (`content_post_id`),
  CONSTRAINT `content_post_thumbnails_ibfk_1` FOREIGN KEY (`content_post_id`) REFERENCES `content_posts` (`id`),
  CONSTRAINT `content_post_thumbnails_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->runSql("CREATE TABLE IF NOT EXISTS `content_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` int unsigned DEFAULT NULL,
  `entity_item_id` int unsigned DEFAULT NULL,
  `original_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `uploaded_by` int unsigned NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_modified_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `width` int unsigned NOT NULL,
  `height` int unsigned NOT NULL,
  `size` int unsigned NOT NULL,
  `mime` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(41) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'sha-1 40 hex chars + 1 vor varchar type',
  `alt` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `wp_id` bigint unsigned DEFAULT NULL,
  `wp_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_button_id` (`entity_item_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `entity_id` (`entity_id`),
  CONSTRAINT `content_images_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_images_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_galleries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` int unsigned NOT NULL,
  `entity_item_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_id` (`entity_id`),
  CONSTRAINT `content_galleries_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_gallery_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `gallery_id` int unsigned NOT NULL,
  `image_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gallery_id_image_id` (`gallery_id`,`image_id`),
  KEY `image_id` (`image_id`),
  CONSTRAINT `content_gallery_images_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `content_galleries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_gallery_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `content_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql("CREATE TABLE IF NOT EXISTS `content_attachments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` int unsigned DEFAULT NULL,
  `entity_item_id` int unsigned DEFAULT NULL,
  `original_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `uploaded_by` int unsigned NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `size` int unsigned NOT NULL,
  `mime` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(41) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'sha-1 40 hex chars + 1 vor varchar type',
  `wp_id` bigint unsigned DEFAULT NULL,
  `wp_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_button_id` (`entity_item_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `entity_id` (`entity_id`),
  CONSTRAINT `content_attachments_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_youtube_records` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` int unsigned NOT NULL,
  `entity_item_id` int unsigned NOT NULL,
  `uploaded_by` int unsigned NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `width` int unsigned NOT NULL,
  `height` int unsigned NOT NULL,
  `youtube_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `youtube_id` (`youtube_id`),
  KEY `social_button_id` (`entity_item_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `entity_id` (`entity_id`),
  CONSTRAINT `content_youtube_records_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_youtube_records_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql('CREATE TABLE IF NOT EXISTS `content_comment_statuses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `codename` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `is_start` int unsigned DEFAULT NULL,
  `is_finish` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->runSql("CREATE TABLE IF NOT EXISTS `content_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL,
  `entity_id` int unsigned NOT NULL,
  `entity_item_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` int unsigned NOT NULL DEFAULT '1',
  `ip_address` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `author_user` int unsigned DEFAULT NULL,
  `author_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_email` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `wp_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_id` (`entity_id`),
  KEY `author_user` (`author_user`),
  KEY `status_id` (`status_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `content_comments_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`),
  CONSTRAINT `content_comments_ibfk_2` FOREIGN KEY (`author_user`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_comments_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `content_comment_statuses` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `content_comments_ibfk_5` FOREIGN KEY (`parent_id`) REFERENCES `content_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1586944713_Create_Content_Tables
