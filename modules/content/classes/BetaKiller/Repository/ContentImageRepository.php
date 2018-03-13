<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentImageInterface;
use BetaKiller\Model\EntityModelInterface;

/**
 * Class ContentImageRepository
 *
 * @package BetaKiller\Content
 * @method ContentImageInterface findById(int $id)
 * @method ContentImageInterface create()
 * @method ContentImageInterface[] getEditorListing(?EntityModelInterface $relatedEntity, ?int $itemID)
 */
class ContentImageRepository extends AbstractHashStrategyOrmBasedAssetsRepository
    implements WordpressAttachmentRepositoryInterface, ContentElementRepositoryInterface
{
    use OrmBasedContentElementRepositoryTrait;
    use OrmBasedRepositoryHasWordpressIdTrait;
    use OrmBasedRepositoryHasWordpressPathTrait;
}
