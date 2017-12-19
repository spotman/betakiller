<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentAttachmentInterface;

/**
 * Class ContentAttachmentRepository
 *
 * @package BetaKiller\Content
 * @method ContentAttachmentInterface findById(int $id)
 * @method ContentAttachmentInterface|null findByWpId(int $id)
 * @method ContentAttachmentInterface create()
 * @method ContentAttachmentInterface[] getAll()
 */
class ContentAttachmentRepository extends AbstractHashStrategyOrmBasedAssetsRepository implements WordpressAttachmentRepositoryInterface
{
    use OrmBasedRepositoryHasWordpressIdTrait;
    use OrmBasedRepositoryHasWordpressPathTrait;
}
