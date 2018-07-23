<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class ContentCategoryRepository
 *
 * @package BetaKiller\Content
 * @method ContentCategoryInterface findById(int $id)
 * @method ContentCategoryInterface|null findByWpID(int $id)
 * @method ContentCategoryInterface create()
 * @method ContentCategoryInterface[] getAll()
 * @method ContentCategoryInterface[] getRoot()
 * @method ContentCategoryInterface[] getChildren(ContentCategoryInterface $parent)
 */
class ContentCategoryRepository extends AbstractOrmBasedSingleParentTreeRepository
    implements RepositoryHasWordpressIdInterface
{
    use OrmBasedRepositoryHasWordpressIdTrait;

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return 'uri';
    }

    /**
     * @return string
     */
    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        $parentCategory = $params->getEntityByClassName(ContentCategory::class);

        /** @var \BetaKiller\Model\ContentCategory $categoryOrm */
        $categoryOrm = $orm;

        $this
            ->filterIsActive($categoryOrm)
            ->filterParent($categoryOrm, $parentCategory);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param bool|null                              $value
     *
     * @return $this
     */
    private function filterIsActive(ExtendedOrmInterface $orm, ?bool $value = null): self
    {
        $orm->where($orm->object_column('is_active'), '=', $value ?? true);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param bool|null                              $desc
     *
     * @return $this
     */
    private function orderByPlace(ExtendedOrmInterface $orm, ?bool $desc = null): self
    {
        $orm->order_by($orm->object_column('place'), ($desc ?? false) ? 'desc' : 'asc');

        return $this;
    }

    protected function customFilterForTreeTraversing(ExtendedOrmInterface $orm): void
    {
        $this->filterIsActive($orm)->orderByPlace($orm);
    }
}
