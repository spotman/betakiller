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

    /**
     * @param array $ids
     *
     * @return \BetaKiller\Model\ContentGalleryInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByImagesWpIDs(array $ids): ?ContentGalleryInterface
    {
        // Keep ids in ascending order (as in sql query)
        sort($ids);

        $orm = $this->getOrmInstance();

        $orm->join_related('images', 'images');
        $orm->having(
            \DB::expr('GROUP_CONCAT(images.wp_id ORDER BY images.wp_id ASC SEPARATOR ",")'),
            '=',
            implode(',', $ids)
        );
        $orm->group_by_primary_key();

        return $this->findOne($orm);
    }
}
