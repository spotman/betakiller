<?php
namespace BetaKiller\Repository;

use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class ContentCategoryRepository
 *
 * @package BetaKiller\Content
 * @method ContentCategoryInterface findById(int $id)
 * @method ContentCategoryInterface|null findByWpID(int $id)
 * @method ContentCategoryInterface create()
 * @method ContentCategoryInterface[] getAll()
 */
class ContentCategoryRepository extends AbstractOrmBasedDispatchableRepository implements
    RepositoryHasWordpressIdInterface
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
     * @return ContentCategoryInterface[]
     */
    public function getRoot(): array
    {
        /** @var ContentCategory $orm */
        $orm = $this->getOrmInstance();

        return $orm->getRoot();
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        $parent_category = $params->getEntityByClassName(ContentCategory::class);

        /** @var \BetaKiller\Model\ContentCategory $categoryOrm */
        $categoryOrm = $orm;

        $categoryOrm->filter_is_active()->filter_parent($parent_category);
    }
}
