<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentGalleryInterface;
use BetaKiller\Model\EntityModelInterface;

/**
 * Class ContentGalleryRepository
 *
 * @package BetaKiller\Content
 * @method ContentGalleryInterface findById(int $id)
 * @method ContentGalleryInterface create()
 * @method ContentGalleryInterface[] getEditorListing(?EntityModelInterface $relatedEntity, ?int $itemID)
 */
class ContentGalleryRepository extends AbstractOrmBasedRepository implements ContentElementRepositoryInterface
{
    use OrmBasedContentElementRepositoryTrait;
}
