<?php

namespace BetaKiller\Model;

class ContentPostThumbnail extends AbstractOrmBasedAssetsImageModel implements ContentPostThumbnailInterface
{
    use OrmBasedEntityHasWordpressIdTrait,
        OrmBasedEntityHasWordpressPathTrait;

    protected function configure(): void
    {
        $this->_table_name = 'content_post_thumbnails';

        $this->belongs_to([
            'post' => [
                'model'       => 'ContentPost',
                'foreign_key' => 'content_post_id',
            ],
        ]);

        parent::configure();
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
