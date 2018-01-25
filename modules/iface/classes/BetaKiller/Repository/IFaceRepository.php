<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\ExtendedOrmInterface;

class IFaceRepository extends AbstractOrmBasedSingleParentTreeRepository
{
    /**
     * @return string
     */
    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return IFaceModelInterface::URL_KEY;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return void
     */
    protected function customFilterForTreeTraversing(ExtendedOrmInterface $orm): void
    {
        // Nothing special here
    }
}
