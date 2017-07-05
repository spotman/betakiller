<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\RepositoryHasWordpressIdAndPathInterface;
use BetaKiller\Model\ContentPostThumbnail;

class ContentPostThumbnailRepository extends AbstractHashStrategyOrmBasedAssetsRepository implements RepositoryHasWordpressIdAndPathInterface
{
    use \Model_ORM_RepositoryHasWordpressIdTrait;
    use \Model_ORM_RepositoryHasWordpressPathTrait;

    /**
     * Creates empty entity
     *
     * @return mixed
     */
    public function create(): ContentPostThumbnail
    {
        return parent::create();
    }
}
