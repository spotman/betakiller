<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\RepositoryHasWordpressIdInterface;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Model\ContentCategory;

class ContentCategoryRepository extends AbstractOrmBasedDispatchableRepository implements RepositoryHasWordpressIdInterface
{
    use \Model_ORM_RepositoryHasWordpressIdTrait;

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        $parent_category = $params->getEntityByClassName(ContentCategory::class);

        /** @var \BetaKiller\Model\ContentCategory $categoryOrm */
        $categoryOrm = $orm;

        $categoryOrm->filter_is_active()->filter_parent($parent_category);
    }
}
