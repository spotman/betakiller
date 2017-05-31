<?php
namespace BetaKiller\Helper;

/**
 * Trait ContentTrait
 *
 * @package BetaKiller\Helper
 * @deprecated Move methods to ContentHelper
 */
trait ContentTrait
{
    /**
     * @return \Service_ContentFacade
     */
    protected function service_content_facade()
    {
        return \Service_ContentFacade::instance();
    }

    /**
     * @return \Service_Content_Youtube
     */
    protected function service_content_youtube()
    {
        return \Service_Content_Youtube::instance();
    }

    /**
     * @return \Assets_Provider_ContentImage|\BetaKiller\Assets\Provider\AbstractAssetsProviderImage
     */
    protected function assets_provider_content_image()
    {
        return \BetaKiller\Assets\AssetsProviderFactory::instance()->create('ContentImage');
    }

    /**
     * @return \Assets_Provider_ContentAttachment|\BetaKiller\Assets\Provider\AbstractAssetsProvider
     */
    protected function assets_provider_content_attachment()
    {
        return \BetaKiller\Assets\AssetsProviderFactory::instance()->create('ContentAttachment');
    }

    /**
     * @return \Assets_Provider_ContentPostThumbnail|\BetaKiller\Assets\Provider\AbstractAssetsProviderImage
     */
    protected function assets_provider_content_post_thumbnail()
    {
        return \BetaKiller\Assets\AssetsProviderFactory::instance()->create('ContentPostThumbnail');
    }

    /**
     * @param int|null $id
     *
     * @return \Model_ContentPost|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    public function model_factory_content_post($id = null)
    {
        return \ORM::factory('ContentPost', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentCategory|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    public function model_factory_content_category($id = null)
    {
        return \ORM::factory('ContentCategory', $id);
    }

    /**
     * @param int|null $id
     * @return \BetaKiller\Model\Entity|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_content_entity($id = NULL)
    {
        return \ORM::factory('Entity', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentImageElement|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_content_image_element($id = NULL)
    {
        return \ORM::factory('ContentImageElement', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentPostThumbnail|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_content_post_thumbnail($id = NULL)
    {
        return \ORM::factory('ContentPostThumbnail', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentAttachmentElement|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_content_attachment_element($id = NULL)
    {
        return \ORM::factory('ContentAttachmentElement', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentYoutubeRecord|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_content_youtube_record($id = NULL)
    {
        return \ORM::factory('ContentYoutubeRecord', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_Quote|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_quote($id = NULL)
    {
        return \ORM::factory('Quote', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentComment|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_content_comment($id = NULL)
    {
        return \ORM::factory('ContentComment', $id);
    }

    /**
     * @return \Model_ContentCommentStatus|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function model_factory_content_comment_status()
    {
        return \ORM::factory('ContentCommentStatus');
    }

    /**
     * @return \CustomTag
     */
    protected function custom_tag_instance()
    {
        return \CustomTag::instance();
    }
}
