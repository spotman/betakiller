<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentAttachmentInterface;
use BetaKiller\Model\EntityModelInterface;

/**
 * Class ContentAttachmentRepository
 *
 * @package BetaKiller\Content
 * @method ContentAttachmentInterface findById(int $id)
 * @method ContentAttachmentInterface|null findByWpId(int $id)
 * @method ContentAttachmentInterface create()
 * @method ContentAttachmentInterface[] getAll()
 * @method ContentAttachmentInterface[] getEditorListing(?EntityModelInterface $relatedEntity, ?int $itemID)
 */
class ContentAttachmentRepository extends AbstractOrmBasedHashStrategyAssetsRepository
    implements WordpressAttachmentRepositoryInterface, ContentElementRepositoryInterface
{
    use OrmBasedContentElementRepositoryTrait;
    use OrmBasedRepositoryHasWordpressIdTrait;
    use OrmBasedRepositoryHasWordpressPathTrait;
}
