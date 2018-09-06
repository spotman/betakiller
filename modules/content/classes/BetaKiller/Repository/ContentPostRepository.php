<?php
namespace BetaKiller\Repository;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Helper\ExceptionTranslator;
use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\ModelWithRevisionsInterface;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class ContentPostRepository
 *
 * @package BetaKiller\Content
 * @method ContentPost|null findById(int $id)
 * @method ContentPost|null findByWpID(int $id)
 * @method ContentPost create()
 * @method ContentPost[] getAll()
 */
class ContentPostRepository extends AbstractOrmBasedDispatchableRepository implements RepositoryHasWordpressIdInterface
{
    use OrmBasedRepositoryHasWordpressIdTrait;

    /**
     * @var \BetaKiller\Repository\ContentCategoryRepository
     */
    private $categoryRepo;

    /**
     * AbstractOrmBasedRepository constructor.
     *
     * @param \BetaKiller\Factory\OrmFactory                   $ormFactory
     * @param \BetaKiller\Repository\ContentCategoryRepository $categoryRepo
     * @param \BetaKiller\Helper\ExceptionTranslator           $translator
     */
    public function __construct(
        OrmFactory $ormFactory,
        ContentCategoryRepository $categoryRepo,
        ExceptionTranslator $translator
    ) {
        parent::__construct($ormFactory, $translator);

        $this->categoryRepo = $categoryRepo;
    }

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return 'uri';
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $parameters): void
    {
        // Load pages first
        $this->prioritizeByPostTypes($orm);

        $category = $parameters->getEntityByClassName(ContentCategory::class);

        $orm->and_where_open();

        // Plain pages
        $orm->or_where_open();
        $this->filterType($orm, ContentPost::TYPE_PAGE);

        // Pages have no category
        $this->filterCategory($orm, null);
        $orm->or_where_close();


        // Articles
        $orm->or_where_open();
        $this->filterType($orm, ContentPost::TYPE_ARTICLE);

        if ($category) {
            // Concrete category
            $this->filterCategory($orm, $category);
        } else {
            // Any category (articles must have category)
            $this->filterWithCategory($orm);
        }

        $orm->or_where_close();

        $orm->and_where_close();
    }

    /**
     * @param ContentCategoryInterface $category
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getCategoryArticles(ContentCategoryInterface $category): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterCategory($orm, $category)
            ->findAll($orm);
    }

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
    ): SearchResultsInterface {
        $orm = $this->getOrmInstance();

        if ($category && $category->hasID()) {
            $categoriesIDs = $this->categoryRepo->getAllChildrenIDs($category, true);
            $this->filterCategoryIDs($orm, $categoriesIDs);
        }

        if ($term) {
            $this->search($orm, $term);
        }

        $this
            ->filterArticles($orm)
            ->orderByCreatedAt($orm);

        return $orm->getSearchResults($page, $itemsPerPage);
    }

    /**
     * @param OrmInterface $orm
     * @param string       $term
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function search(OrmInterface $orm, string $term): ContentPostRepository
    {
        $revisionKey = ModelWithRevisionsInterface::ACTUAL_REVISION_KEY;

        $orm->search_query($term, [
            $revisionKey.'.label',
            $revisionKey.'.content',
        ]);

        return $this;
    }

    /**
     * @param int|null $limit
     * @param int|null $excludeID
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPopularArticles(?int $limit = null, $excludeID = null): array
    {
        return $this->getPopularContent(ContentPost::TYPE_ARTICLE, $limit, $excludeID);
    }

    /**
     * @param int|null $limit
     * @param int|null $excludeID
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getFreshArticles(?int $limit = null, $excludeID = null): array
    {
        return $this->getFreshContent(ContentPost::TYPE_ARTICLE, $limit, $excludeID);
    }

    /**
     * @param int|null $limit
     *
     * @return ContentPost[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAllArticles(?int $limit = null): array
    {
        $orm = $this->getOrmInstance();

        if ($limit) {
            $this->limit($orm, $limit);
        }

        return $this
            ->filterArticles($orm)
            ->findAll($orm);
    }

    /**
     * @return ContentPost[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAllPages(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterPages($orm)
            ->findAll($orm);
    }

    /**
     * @param int|int[]|null $filterType
     * @param int            $limit
     * @param int|int[]|null $excludeID
     *
     * @return ContentPost[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getPopularContent($filterType, ?int $limit, $excludeID = null): array
    {
        $orm = $this->getOrmInstance();

        if ($excludeID) {
            $orm->filter_ids((array)$excludeID, true);
        }

        return $this
            ->filterTypes($orm, (array)$filterType)
            ->orderByViewsCount($orm)
            ->limit($orm, $limit ?? 5)
            ->findAll($orm);
    }

    /**
     * @param int|int[]|null $filterType
     * @param int            $limit
     * @param int|int[]|null $excludeID
     *
     * @return \BetaKiller\Model\ContentPostInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getFreshContent($filterType, ?int $limit = null, $excludeID = null): array
    {
        $orm = $this->getOrmInstance();

        if ($excludeID) {
            $orm->filter_ids((array)$excludeID, true);
        }

        $this
            ->filterTypes($orm, (array)$filterType)
            ->orderByViewsCount($orm)
            ->limit($orm, $limit ?? 5);

        return $this->findAll($orm);
    }

    /**
     * @param OrmInterface $orm
     * @param array        $ids
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function filterCategoryIDs(OrmInterface $orm, array $ids): ContentPostRepository
    {
        $orm->where($orm->object_column('category_id'), 'IN', $ids);

        return $this;
    }

    /**
     * @param OrmInterface             $orm
     * @param ContentCategoryInterface $category
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function filterCategory(OrmInterface $orm, ?ContentCategoryInterface $category): ContentPostRepository
    {
        $column = $orm->object_column('category_id');

        $category
            ? $orm->where($column, '=', $category->getID())
            : $orm->where($column, 'IS', null);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function filterArticles(OrmInterface $orm): ContentPostRepository
    {
        return $this->filterType($orm, ContentPost::TYPE_ARTICLE);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function filterPages(OrmInterface $orm): ContentPostRepository
    {
        return $this->filterType($orm, ContentPost::TYPE_PAGE);
    }

    /**
     * @param OrmInterface $orm
     * @param int[]        $values
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function filterTypes(OrmInterface $orm, array $values): ContentPostRepository
    {
        $orm->where('type', 'IN', $values);

        return $this;
    }

    /**
     * @param OrmInterface $orm
     * @param int          $value
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function filterType(OrmInterface $orm, int $value): ContentPostRepository
    {
        $orm->where('type', '=', $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function filterWithCategory(OrmInterface $orm): ContentPostRepository
    {
        $orm->where($orm->object_column('category_id'), 'IS NOT', null);

        return $this;
    }

    /**
     * @param OrmInterface $orm
     * @param bool|null    $asc
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null): ContentPostRepository
    {
        $orm->order_by($orm->object_column('created_at'), ($asc ?? false) ? 'ASC' : 'DESC');

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function prioritizeByPostTypes(OrmInterface $orm): ContentPostRepository
    {
        return $this->orderByPostTypes($orm, ContentPost::getPrioritizedTypesList());
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int[]                                     $values
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function orderByPostTypes(OrmInterface $orm, array $values): ContentPostRepository
    {
        $orm->order_by_field_sequence('type', $values);

        return $this;
    }

    /**
     * @param OrmInterface $orm
     * @param bool|null    $asc
     *
     * @return \BetaKiller\Repository\ContentPostRepository
     */
    private function orderByViewsCount(OrmInterface $orm, ?bool $asc = null): ContentPostRepository
    {
        $orm->order_by('views_count', ($asc ?? false) ? 'ASC' : 'DESC');

        return $this;
    }
}
