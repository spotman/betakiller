<?php

namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AbstractAssetsOrmImageModel;

class ContentPostThumbnail extends AbstractAssetsOrmImageModel implements ContentPostThumbnailInterface
{
    use OrmBasedEntityHasWordpressIdTrait,
        OrmBasedEntityHasWordpressPathTrait;

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
    public function getPost(): ContentPost
    {
        return $this->get('post');
    }

    /**
     * @param $post \BetaKiller\Model\ContentPost
     */
    public function setPost(ContentPost $post): void
    {
        $this->set('post', $post);
    }
}
