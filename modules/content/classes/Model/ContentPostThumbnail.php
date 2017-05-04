<?php

use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Model\AbstractAssetsOrmModelSeoImage;
use BetaKiller\Content\ImportedFromWordpressWithPathInterface;

class Model_ContentPostThumbnail extends AbstractAssetsOrmModelSeoImage implements ImportedFromWordpressWithPathInterface
{
    use Model_ORM_ImportedFromWordpressTrait,
        Model_ORM_HasWordpressPathTrait;

    protected function _initialize()
    {
        $this->_table_name = 'content_post_thumbnails';

        $this->belongs_to([
            'post'              =>  [
                'model'         =>  'ContentPost',
                'foreign_key'   =>  'content_post_id',
            ],
        ]);

        parent::_initialize();
    }

    /**
     * @return Model_ContentPost
     */
    public function get_post()
    {
        return $this->get('post');
    }

    /**
     * @param $post Model_ContentPost
     *
     * @return $this
     */
    public function set_post(Model_ContentPost $post)
    {
        return $this->set('post', $post);
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return Assets_Provider_ContentPostThumbnail
     */
    protected function getProvider()
    {
        return AssetsProviderFactory::instance()->create('ContentPostThumbnail');
    }
}
