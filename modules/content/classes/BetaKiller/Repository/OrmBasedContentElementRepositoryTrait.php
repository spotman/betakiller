<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\EntityModelInterface;

trait OrmBasedContentElementRepositoryTrait
{
    use OrmBasedEntityItemRelatedRepositoryTrait;

    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Model\ContentElementInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEditorListing(?EntityModelInterface $relatedEntity, ?int $itemID): array
    {
        $orm = $this->getOrmInstance();

        $this->filterEntityAndEntityItemID($orm, $relatedEntity, $itemID);

        return $this->findAll($orm);
    }
}
