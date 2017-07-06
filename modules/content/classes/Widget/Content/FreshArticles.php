<?php

use BetaKiller\Helper\ContentHelper;
use BetaKiller\Model\ContentPost;

class Widget_Content_FreshArticles extends Widget_Content_SidebarArticlesList
{
    /**
     * @var ContentHelper
     * @Inject
     */
    private $contentHelper;

    /**
     * @param int $exclude_id
     * @param int $limit
     *
     * @return ContentPost[]
     */
    protected function get_articles_list($exclude_id, $limit)
    {
        return $this->contentHelper->getPostRepository()->getFreshArticles($limit, $exclude_id);
    }
}
