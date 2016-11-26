<?php
namespace BetaKiller\Helper;

use Model_ContentCategory;

trait ContentUrlParametersTrait
{
    /**
     * @return \Model_ContentCategory
     */
    public function get_content_category()
    {
        return $this->get(Model_ContentCategory::URL_PARAM);
    }

    /**
     * @param \Model_ContentCategory $model
     *
     * @return static
     */
    public function set_content_category(Model_ContentCategory $model)
    {
        return $this->set(Model_ContentCategory::URL_PARAM, $model, TRUE);
    }
}
