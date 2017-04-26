<?php
namespace BetaKiller\Helper;

use Model_ContentCategory;
use BetaKiller\IFace\Url\UrlParametersInterface;

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
}
