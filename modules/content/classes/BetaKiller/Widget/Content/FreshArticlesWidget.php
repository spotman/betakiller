<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\IFaceHelper;
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
     * @param \BetaKiller\Helper\IFaceHelper                  $ifaceHelper
     */
    public function __construct(
        UrlContainerInterface $urlContainer,
        AssetsHelper $assetsHelper,
        IFaceHelper $ifaceHelper,
        ContentPostRepository $postRepo
    ) {
        parent::__construct($urlContainer, $assetsHelper, $ifaceHelper);

        $this->postRepo = $postRepo;
    }

    /**
     * @param int $exclude_id
     * @param int $limit
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getArticlesList($exclude_id, $limit): array
    {
        return $this->postRepo->getFreshArticles($limit, $exclude_id);
    }
}
