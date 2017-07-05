<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\ContentAttachmentInterface;

class ContentAttachmentRepository extends AbstractHashStrategyOrmBasedAssetsRepository implements WordpressAttachmentRepositoryInterface
{
    use \Model_ORM_RepositoryHasWordpressIdTrait;
    use \Model_ORM_RepositoryHasWordpressPathTrait;

    /**
     * Creates empty entity
     *
     * @return mixed
     */
    public function create(): ContentAttachmentInterface
    {
        return parent::create();
    }
}
