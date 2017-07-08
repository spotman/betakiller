<?php
namespace BetaKiller\Model;

interface ContentPostThumbnailInterface extends WordpressAttachmentInterface
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
