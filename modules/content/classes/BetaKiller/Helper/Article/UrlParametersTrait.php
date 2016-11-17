<?php
namespace BetaKiller\Helper\Article;

use BetaKiller\Helper\IFace;
use Model_ContentArticle;
use Model_ContentCategory;

trait UrlParametersTrait
{
    /**
     * @return \Model_ContentArticle
     */
    public function get_article()
    {
        return $this->get(Model_ContentArticle::URL_PARAM);
    }

    /**
     * @param \Model_ContentArticle $model
     *
     * @return static
     */
    public function set_article(\Model_ContentArticle $model)
    {
        return $this->set(Model_ContentArticle::URL_PARAM, $model);
    }

    /**
     * @return \Model_ContentCategory
     */
    public function get_article_category()
    {
        return $this->get(Model_ContentCategory::URL_PARAM);
    }

    /**
     * @param \Model_ContentCategory $model
     *
     * @return static
     */
    public function set_article_category(Model_ContentCategory $model)
    {
        return $this->set(Model_ContentCategory::URL_PARAM, $model, TRUE);
    }
}
