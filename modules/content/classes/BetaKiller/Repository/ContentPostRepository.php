<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\RepositoryHasWordpressIdInterface;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\RevisionModelInterface;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateTimeInterface;

class ContentPostRepository extends AbstractOrmBasedDispatchableRepository implements RepositoryHasWordpressIdInterface
{
    use \Model_ORM_RepositoryHasWordpressIdTrait;

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
     * @param ContentCategory $category
     *
     * @return ContentPost[]
     */
    public function getCategoryArticles(ContentCategory $category): array
    {
        $orm = $this->getOrmInstance();

        $this->filterCategory($orm, $category);

        return $orm->get_all();
    }

    public function searchArticles(
        int $page,
        int $itemsPerPage,
        ContentCategory $category = null,
        ?string $term = null
    ): SearchResultsInterface {
        $orm = $this->getOrmInstance();

        if ($category && $category->get_id()) {
            $categories_ids = $category->get_all_related_categories_ids();
            $this->filterCategoryIDs($orm, $categories_ids);
        }

        if ($term) {
            $this->search($orm, $term);
        }

        $this->filterArticles($orm);
        $this->orderByCreatedAt($orm);

        return $orm->getSearchResults($page, $itemsPerPage);
    }


    /**
     * @param OrmInterface $orm
     * @param string       $term
     */
    private function search(OrmInterface $orm, string $term): void
    {
        $revisionKey = RevisionModelInterface::ACTUAL_REVISION_KEY;

        $orm->search_query($term, [
            $revisionKey.'.label',
            $revisionKey.'.content',
        ]);
    }

    /**
     * @param int|null $limit
     * @param int|null $excludeID
     *
     * @return ContentPost[]
     */
    public function getPopularArticles(?int $limit = null, $excludeID = null): array
    {
        return $this->getPopularContent(ContentPost::TYPE_ARTICLE, $limit, $excludeID);
    }

    /**
     * @param int|null $limit
     * @param int|null $exclude_id
     *
     * @return ContentPost[]
     */
    public function getFreshArticles(?int $limit = null, $exclude_id = null): array
    {
        return $this->getFreshContent(ContentPost::TYPE_ARTICLE, $limit, $exclude_id);
    }

    /**
     * @param int|null $limit
     *
     * @return ContentPost[]
     */
    public function getAllArticles(?int $limit = null): array
    {
        $orm = $this->getOrmInstance();

        if ($limit) {
            $orm->limit($limit);
        }

        $this->filterArticles($orm);

        return $orm->get_all();
    }

    /**
     * @return ContentPost[]
     */
    public function getAllPages(): array
    {
        $orm = $this->getOrmInstance();

        $this->filterPages($orm);

        return $orm->get_all();
    }

    /**
     * @param int|int[]|null $filter_type
     * @param int            $limit
     * @param int|int[]|null $exclude_id
     *
     * @return ContentPost[]
     */
    private function getPopularContent($filter_type, ?int $limit, $exclude_id = null): array
    {
        $orm = $this->getOrmInstance();

        if ($exclude_id) {
            $orm->filter_ids((array)$exclude_id, true);
        }

        $this->filterTypes($orm, (array)$filter_type);
        $this->orderByViewsCount($orm);
        $orm->limit($limit ?? 5);

        return $orm->get_all();
    }

    /**
     * @param int|int[]|null $filter_type
     * @param int            $limit
     * @param int|int[]|null $exclude_id
     *
     * @return ContentPost[]
     */
    private function getFreshContent($filter_type, ?int $limit = null, $exclude_id = null): array
    {
        $orm = $this->getOrmInstance();

        if ($exclude_id) {
            $orm->filter_ids((array)$exclude_id, true);
        }

        $this->filterTypes($orm, (array)$filter_type);
        $this->orderByViewsCount($orm);
        $orm->limit($limit ?? 5);

        return $orm->get_all();
    }

    /**
     * @param OrmInterface $orm
     * @param array        $ids
     */
    private function filterCategoryIDs(OrmInterface $orm, array $ids): void
    {
        $orm->where($orm->object_column('category_id'), 'IN', $ids);
    }

    /**
     * @param OrmInterface    $orm
     * @param ContentCategory $category
     */
    private function filterCategory(OrmInterface $orm, ?ContentCategory $category): void
    {
        $column = $orm->object_column('category_id');

        $category
            ? $orm->where($column, '=', $category->get_id())
            : $orm->where($column, 'IS', null);
    }

//    /**
//     * @param OrmInterface       $orm
//     * @param \DateTimeInterface $date
//     */
//    private function filterPostsBefore(OrmInterface $orm, DateTimeInterface $date): void
//    {
//        $this->filterCreatedBy($orm, $date, '<');
//        $this->orderByCreatedAt($orm);
//    }

    /**
     * @param OrmInterface       $orm
     * @param \DateTimeInterface $date
     * @param null|string        $op
     */
    private function filterCreatedBy(OrmInterface $orm, DateTimeInterface $date, ?string $op = null): void
    {
        $orm->filter_datetime_column_value('created_at', $date, $op ?? '<');
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     */
    private function filterArticles(OrmInterface $orm): void
    {
        $this->filterType($orm, ContentPost::TYPE_ARTICLE);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     */
    private function filterPages(OrmInterface $orm): void
    {
        $this->filterType($orm, ContentPost::TYPE_PAGE);
    }

    /**
     * @param OrmInterface $orm
     * @param int[]        $values
     */
    private function filterTypes(OrmInterface $orm, array $values): void
    {
        $orm->where('type', 'IN', $values);
    }

    /**
     * @param OrmInterface $orm
     * @param int          $value
     */
    private function filterType(OrmInterface $orm, int $value): void
    {
        $orm->where('type', '=', $value);
    }

//    /**
//     * @param OrmInterface $orm
//     * @param string       $value
//     */
//    private function filterUri(OrmInterface $orm, string $value): void
//    {
//        $orm->where($orm->object_column('uri'), '=', $value);
//    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     */
    private function filterWithCategory(OrmInterface $orm): void
    {
        $orm->where($orm->object_column('category_id'), 'IS NOT', null);
    }

    /**
     * @param OrmInterface $orm
     * @param bool|null    $asc
     */
    private function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null): void
    {
        $orm->order_by($orm->object_column('created_at'), ($asc ?? false) ? 'ASC' : 'DESC');
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     */
    private function prioritizeByPostTypes(OrmInterface $orm): void
    {
        $this->orderByPostTypes($orm, ContentPost::getPrioritizedTypesList());
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int[]                                     $values
     */
    private function orderByPostTypes(OrmInterface $orm, array $values): void
    {
        $orm->order_by_field_sequence('type', $values);
    }

    /**
     * @param OrmInterface $orm
     * @param bool|null    $asc
     */
    private function orderByViewsCount(OrmInterface $orm, ?bool $asc = null): void
    {
        $orm->order_by('views_count', ($asc ?? false) ? 'ASC' : 'DESC');
    }
}
