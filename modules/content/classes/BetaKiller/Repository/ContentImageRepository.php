<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentImageInterface;

/**
 * Class ContentImageRepository
 *
 * @package BetaKiller\Content
 * @method ContentImageInterface findById(int $id)
 * @method ContentImageInterface create()
 */
class ContentImageRepository extends AbstractHashStrategyOrmBasedAssetsRepository implements WordpressAttachmentRepositoryInterface
{
    use OrmBasedContentElementRepositoryTrait;
    use OrmBasedRepositoryHasWordpressIdTrait;
    use OrmBasedRepositoryHasWordpressPathTrait;
}
