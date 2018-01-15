<?php
namespace BetaKiller\Widget\Content;

class PopularArticlesWidget extends SidebarArticlesListWidget
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
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getArticlesList($exclude_id, $limit): array
    {
        return $this->contentHelper->getPostRepository()->getPopularArticles($limit, $exclude_id);
    }
}
