<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\ContentImageInterface;

class ContentImageRepository extends AbstractHashStrategyOrmBasedAssetsRepository implements WordpressAttachmentRepositoryInterface
{
    use \Model_ORM_EntityRelatedRepositoryTrait;
    use \Model_ORM_RepositoryHasWordpressIdTrait;
    use \Model_ORM_RepositoryHasWordpressPathTrait;

    /**
     * Creates empty entity
     *
     * @return mixed
     */
    public function create(): ContentImageInterface
    {
        return parent::create();
    }
}
