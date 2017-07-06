<?php

use BetaKiller\Model\ContentPost;

class Widget_Content_PopularArticles extends Widget_Content_SidebarArticlesList
{
    /**
     * @var \BetaKiller\Helper\ContentHelper
     * @Inject
     */
    private $contentHelper;

    /**
     * @param int $exclude_id
     * @param int $limit
     *
     * @return ContentPost[]
     */
    protected function get_articles_list($exclude_id, $limit): array
    {
        return $this->contentHelper->getPostRepository()->getPopularArticles($limit, $exclude_id);
    }
}
