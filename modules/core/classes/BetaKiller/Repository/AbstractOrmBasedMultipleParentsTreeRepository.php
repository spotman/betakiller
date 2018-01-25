<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\SingleParentTreeModelInterface;

abstract class AbstractOrmBasedMultipleParentsTreeRepository extends AbstractOrmBasedDispatchableRepository
    implements MultipleParentsTreeRepositoryInterface
{
}
