<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1479622818_Merge_Articles_And_Pages extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1479622818;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'Merge_articles_and_pages';
	}

	/**
	 * Returns migration info
	 *
	 * @return string
	 */
	public function description()
	{
		return '';
	}

	/**
	 * Takes a migration
	 *
	 * @return void
	 */
	public function up()
	{
        $this->run_sql("CREATE TABLE IF NOT EXISTS `content_post_types` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->run_sql("INSERT INTO `content_post_types` (`id`, `name`) VALUES (1,	'post'), (2,	'page');");

        if ($this->table_exists('content_articles'))
        {
            $this->run_sql("ALTER TABLE `content_articles` RENAME TO `content_posts`;");

            $this->run_sql("ALTER TABLE `content_posts`
              ADD `type` int(11) unsigned NOT NULL DEFAULT '1' AFTER `id`,
              ADD INDEX `type` (`type`),
              ADD FOREIGN KEY (`type`) REFERENCES `content_post_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;");

            // Preset default type for existing records
            $this->run_sql("UPDATE `content_posts` SET `type` = 1;");
        }

        if ($this->table_exists('content_articles_thumbnails'))
        {
            $this->run_sql("ALTER TABLE `content_articles_thumbnails`
              CHANGE `article_id` `post_id` int(11) unsigned NOT NULL FIRST,
              RENAME TO `content_posts_thumbnails`;");
        }

        $this->run_sql("DROP TABLE IF EXISTS `content_pages`;");
    }

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{

	}

} // End Migration1479622818_Merge_Articles_And_Pages
