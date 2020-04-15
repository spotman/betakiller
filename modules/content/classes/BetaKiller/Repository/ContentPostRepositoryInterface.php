<?php
namespace BetaKiller\Repository;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\ModelWithRevisionsInterface;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Interface ContentPostRepositoryInterface
 *
 * @package BetaKiller\Content
 * @method ContentPost|null findById(int $id)
 * @method ContentPost|null findByWpID(int $id)
 * @method ContentPost[] getAll()
 */
interface ContentPostRepositoryInterface extends DispatchableRepositoryInterface, RepositoryHasWordpressIdInterface
{
    /**
     * @param ContentCategoryInterface $category
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getCategoryArticles(ContentCategoryInterface $category): array;

    /**
     * @param int                                        $page
     * @param int                                        $itemsPerPage
     * @param \BetaKiller\Model\ContentCategoryInterface $category
     * @param null|string                                $term
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function searchArticles(
        int $page,
        int $itemsPerPage,
        ContentCategoryInterface $category = null,
        ?string $term = null
    ): SearchResultsInterface;

    /**
     * @param int|null $limit
     * @param int|null $excludeID
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPopularArticles(?int $limit = null, $excludeID = null): array;

    /**
     * @param int|null $limit
     * @param int|null $excludeID
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getFreshArticles(?int $limit = null, $excludeID = null): array;

    /**
     * @param int|null $limit
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAllArticles(?int $limit = null): array;

    /**
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAllPages(): array;
}
