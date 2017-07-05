<?php

namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AbstractAssetsOrmImageModel;
use BetaKiller\Content\EntityHasWordpressIdAndPathInterface;
use Model_ORM_EntityHasWordpressIdTrait;
use Model_ORM_EntityHasWordpressPathTrait;

class ContentPostThumbnail extends AbstractAssetsOrmImageModel implements EntityHasWordpressIdAndPathInterface
{
    use Model_ORM_EntityHasWordpressIdTrait,
        Model_ORM_EntityHasWordpressPathTrait;

    protected function _initialize(): void
    {
        $this->_table_name = 'content_post_thumbnails';

        $this->belongs_to([
            'post' => [
                'model'       => 'ContentPost',
                'foreign_key' => 'content_post_id',
            ],
        ]);

        parent::_initialize();
    }

    /**
     * @return \BetaKiller\Model\ContentPost
     */
    public function get_post(): ContentPost
    {
        return $this->get('post');
    }

    /**
     * @param $post \BetaKiller\Model\ContentPost
     *
     * @return $this
     */
    public function set_post(ContentPost $post)
    {
        return $this->set('post', $post);
    }
}
