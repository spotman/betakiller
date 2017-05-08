<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Url\UrlParametersInterface;
use Model_ContentCategory;
use Model_ContentPost;

class ContentUrlParametersHelper extends AbstractUrlParametersHelper
{
    /**
     * @return \Model_ContentCategory
     */
    public function getContentCategory()
    {
        return $this->get(Model_ContentCategory::URL_PARAM);
    }

    /**
     * @param \Model_ContentCategory                            $model
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setContentCategory(Model_ContentCategory $model, UrlParametersInterface $params = null)
    {
        return $this->set(Model_ContentCategory::URL_PARAM, $model, $params, true);
    }

    /**
     * @return \Model_ContentPost|null
     */
    public function getContentPost()
    {
        return $this->get(\Model_ContentPost::URL_PARAM);
    }

    /**
     * @param \Model_ContentPost                                $model
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $params
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setContentPost(Model_ContentPost $model, UrlParametersInterface $params = null)
    {
        return $this->set(\Model_ContentPost::URL_PARAM, $model, $params, true);
    }
}
