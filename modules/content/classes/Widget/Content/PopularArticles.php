<?php

class Widget_Content_PopularArticles extends Widget_Content_SidebarArticlesList
{
    use \BetaKiller\Helper\ContentTrait;

    /**
     * @param int $exclude_id
     * @param int $limit
     *
     * @return Model_ContentPost[]
     */
    protected function get_articles_list($exclude_id, $limit)
    {
        return $this->model_factory_content_post()->getPopularArticles($limit, $exclude_id);
    }
}
