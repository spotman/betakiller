<?php
namespace BetaKiller\Helper;

trait Article
{
    /**
     * @param int|null $id
     *
     * @return \Model_ContentArticle
     */
    public function model_factory_content_article($id = null)
    {
        return \ORM::factory('ContentArticle', $id);
    }

    /**
     * @param int|null $id
     *
     * @return \Model_ContentPage
     */
    public function model_factory_content_page($id = null)
    {
        return \ORM::factory('ContentPage', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentCategory
     */
    public function model_factory_content_category($id = null)
    {
        return \ORM::factory('ContentCategory', $id);
    }

    /**
     * @return \Model_ContentArticle
     */
    public function url_parameter_content_article()
    {
        return $this->url_parameters()->get(\Model_ContentArticle::URL_PARAM);
    }

    /**
     * @return \Model_ContentPage
     */
    public function url_parameter_content_page()
    {
        return $this->url_parameters()->get(\Model_ContentPage::URL_PARAM);
    }
}
