<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Url\UrlParametersInterface;
use Model_ContentCategory;
use Model_ContentPost;
use Model_ContentComment;
use Model_ContentCommentStatus;

class ContentUrlParametersHelper extends UrlParametersHelper
{
    /**
     * @return \Model_ContentCategory
     */
    public function getContentCategory(): Model_ContentCategory
    {
        return $this->getEntityByClassName(Model_ContentCategory::class);
    }

    /**
     * @param \Model_ContentCategory                            $model
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setContentCategory(Model_ContentCategory $model, UrlParametersInterface $params = null): UrlParametersInterface
    {
        return $this->setEntity($model, $params);
    }

    /**
     * @return \Model_ContentPost|null
     */
    public function getContentPost(): ?Model_ContentPost
    {
        return $this->getEntityByClassName(Model_ContentPost::class);
    }

    /**
     * @param \Model_ContentPost                                $model
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setContentPost(Model_ContentPost $model, UrlParametersInterface $params = null): \BetaKiller\IFace\Url\UrlParametersInterface
    {
        return $this->setEntity($model, $params);
    }

    /**
     * @return \Model_ContentComment
     */
    public function getContentComment(): Model_ContentComment
    {
        return $this->getEntityByClassName(Model_ContentComment::class);
    }

    /**
     * @param \Model_ContentComment                             $model
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setContentComment(Model_ContentComment $model, UrlParametersInterface $params = null): UrlParametersInterface
    {
        return $this->setEntity($model, $params);
    }

    /**
     * @return \Model_ContentCommentStatus
     */
    public function getContentCommentStatus(): Model_ContentCommentStatus
    {
        return $this->getEntityByClassName(Model_ContentCommentStatus::class);
    }

    /**
     * @param \Model_ContentCommentStatus                       $model
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setContentCommentStatus(\Model_ContentCommentStatus $model, UrlParametersInterface $params = null): UrlParametersInterface
    {
        return $this->setEntity($model, $params);
    }
}
