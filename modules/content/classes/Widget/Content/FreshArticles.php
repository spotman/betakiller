<?php

use BetaKiller\Model\ContentPost;
use BetaKiller\Helper\ContentHelper;

class Widget_Content_FreshArticles extends Widget_Content_SidebarArticlesList
{
    use \BetaKiller\Helper\ContentTrait;

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
        $this->contentHelper->getPostRepository()->getFreshArticles($limit, $exclude_id);
    }
}
