<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\ContentImageInterface;

/**
 * Class ContentImageRepository
 *
 * @package BetaKiller\Content
 * @method ContentImageInterface findById(int $id)
 * @method ContentImageInterface create()
 */
class ContentImageRepository extends AbstractHashStrategyOrmBasedAssetsRepository implements WordpressAttachmentRepositoryInterface
{
    use \Model_ORM_EntityRelatedRepositoryTrait;
    use \Model_ORM_RepositoryHasWordpressIdTrait;
    use \Model_ORM_RepositoryHasWordpressPathTrait;
}
