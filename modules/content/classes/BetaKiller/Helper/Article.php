<?php
namespace BetaKiller\Helper;

trait Article
{
    /**
     * @param int|null $id
     *
     * @return \Model_ContentItem
     */
    public function model_factory_content_item($id = null)
    {
        return \ORM::factory('ContentItem', $id);
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
     * @return \Model_ContentItem
     */
    public function url_parameter_content_item()
    {
        return $this->url_parameters()->get(\Model_ContentItem::URL_PARAM);
    }

    /**
     * @return \Model_ContentCategory
     */
    public function url_parameter_content_category()
    {
        return $this->url_parameters()->get(\Model_ContentCategory::URL_PARAM);
    }
}
