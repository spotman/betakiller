<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentPostThumbnailInterface;

/**
 * Class ContentPostThumbnailRepository
 *
 * @package BetaKiller\Content
 * @method ContentPostThumbnailInterface create()
 * @method ContentPostThumbnailInterface|null findById(int $id)
 * @method ContentPostThumbnailInterface|null findByWpId(int $id)
 * @method ContentPostThumbnailInterface[] getAll()
 */
class ContentPostThumbnailRepository extends AbstractOrmBasedHashStrategyAssetsRepository implements
    WordpressAttachmentRepositoryInterface, EntityItemRelatedRepositoryInterface
{
    use OrmBasedRepositoryHasWordpressIdTrait;
    use OrmBasedRepositoryHasWordpressPathTrait;
    use OrmBasedEntityItemRelatedRepositoryTrait;
}
