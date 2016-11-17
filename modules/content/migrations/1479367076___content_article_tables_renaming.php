<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1479367076_Content_Article_Tables_Renaming extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1479367076;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'Content_article_tables_renaming';
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
        $this->run_sql("ALTER TABLE `article_categories` RENAME TO `content_categories`");
        $this->run_sql("ALTER TABLE `articles` RENAME TO `content_articles`");
        $this->run_sql("ALTER TABLE `articles_thumbnails` RENAME TO `content_articles_thumbnails`");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{
        $this->run_sql("ALTER TABLE `content_categories` RENAME TO `article_categories`");
        $this->run_sql("ALTER TABLE `content_articles` RENAME TO `articles`");
        $this->run_sql("ALTER TABLE `content_articles_thumbnails` RENAME TO `articles_thumbnails`");
	}

} // End Migration1479367076_Content_Article_Tables_Renaming
