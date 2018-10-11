<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Repository\ContentPostRepository;
use BetaKiller\Url\Container\UrlContainerInterface;

class FreshArticlesWidget extends SidebarArticlesListWidget
{
    /**
     * @var \BetaKiller\Repository\ContentPostRepository
     */
    private $postRepo;

    /**
     * SidebarArticlesListWidget constructor.
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param \BetaKiller\Helper\AssetsHelper                 $assetsHelper
     * @param \BetaKiller\Repository\ContentPostRepository    $postRepo
     */
    public function __construct(
        UrlContainerInterface $urlContainer,
        AssetsHelper $assetsHelper,
        ContentPostRepository $postRepo
    ) {
        parent::__construct($urlContainer, $assetsHelper);

        $this->postRepo = $postRepo;
    }

    /**
     * @param int $excludeID
     * @param int $limit
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getArticlesList($excludeID, $limit): array
    {
        return $this->postRepo->getFreshArticles($limit, $excludeID);
    }
}
