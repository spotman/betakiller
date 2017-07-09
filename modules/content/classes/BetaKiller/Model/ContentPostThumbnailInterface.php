<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AssetsModelImageInterface;

interface ContentPostThumbnailInterface extends WordpressAttachmentInterface, AssetsModelImageInterface
{
    /**
     * @return \BetaKiller\Model\ContentPost
     */
    public function getPost(): ContentPost;

    /**
     * @param $post \BetaKiller\Model\ContentPost
     */
    public function setPost(ContentPost $post): void;
}
