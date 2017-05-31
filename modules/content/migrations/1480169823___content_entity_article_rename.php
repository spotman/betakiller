<?php use BetaKiller\Model\Entity;

defined('SYSPATH') or die('No direct access allowed.');

class Migration1480169823_Content_Entity_Article_Rename extends Migration {

    use \BetaKiller\Helper\ContentTrait;

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1480169823;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'Content_entity_article_rename';
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
	    $entity = $this->model_factory_content_entity(Entity::POSTS_ENTITY_ID);

	    $content_item_model = $this->model_factory_content_post();

	    $entity
            ->setLinkedModelName($content_item_model->getModelName())
            ->setSlug('post')
            ->save();
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{

	}

} // End Migration1480169823_Content_Entity_Article_Rename
