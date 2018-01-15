<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Helper\ContentHelper;

class FreshArticlesWidget extends SidebarArticlesListWidget
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
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getArticlesList($exclude_id, $limit): array
    {
        return $this->contentHelper->getPostRepository()->getFreshArticles($limit, $exclude_id);
    }
}
