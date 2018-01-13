<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\EntityModelInterface;

interface ContentElementRepositoryInterface extends EntityItemRelatedRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Model\ContentElementInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEditorListing(?EntityModelInterface $relatedEntity, ?int $itemID): array;
}
