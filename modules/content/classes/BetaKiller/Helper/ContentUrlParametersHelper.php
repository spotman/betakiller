<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentComment;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\ContentCommentStatus;

class ContentUrlParametersHelper extends UrlParametersHelper
{
    /**
     * @return \BetaKiller\Model\ContentCategory|null
     */
    public function getContentCategory(): ?ContentCategory
    {
        return $this->getEntityByClassName(ContentCategory::class);
    }

    /**
     * @param \BetaKiller\Model\ContentCategory                $model
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function setContentCategory(ContentCategory $model, UrlContainerInterface $params = null): UrlContainerInterface
    {
        return $this->setEntity($model, $params);
    }

    /**
     * @return \BetaKiller\Model\ContentPost|null
     */
    public function getContentPost(): ?ContentPost
    {
        return $this->getEntityByClassName(ContentPost::class);
    }

    /**
     * @param \BetaKiller\Model\ContentPost                    $model
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function setContentPost(ContentPost $model, UrlContainerInterface $params = null): UrlContainerInterface
    {
        return $this->setEntity($model, $params);
    }

    /**
     * @return \BetaKiller\Model\ContentComment|null
     */
    public function getContentComment(): ?ContentComment
    {
        return $this->getEntityByClassName(ContentComment::class);
    }

    /**
     * @param \BetaKiller\Model\ContentComment                 $model
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function setContentComment(ContentComment $model, UrlContainerInterface $params = null): UrlContainerInterface
    {
        return $this->setEntity($model, $params);
    }

    /**
     * @return \BetaKiller\Model\ContentCommentStatus|null
     */
    public function getContentCommentStatus(): ?ContentCommentStatus
    {
        return $this->getEntityByClassName(ContentCommentStatus::class);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentStatus           $model
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function setContentCommentStatus(ContentCommentStatus $model, UrlContainerInterface $params = null): UrlContainerInterface
    {
        return $this->setEntity($model, $params);
    }
}
