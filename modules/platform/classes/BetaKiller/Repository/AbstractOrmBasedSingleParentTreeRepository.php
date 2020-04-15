<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

abstract class AbstractOrmBasedSingleParentTreeRepository extends AbstractOrmBasedDispatchableRepository
    implements SingleParentTreeRepositoryInterface
{
    use OrmBasedSingleParentTreeRepositoryTrait;
}
