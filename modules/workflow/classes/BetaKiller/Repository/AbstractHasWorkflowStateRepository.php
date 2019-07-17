<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

abstract class AbstractHasWorkflowStateRepository extends AbstractOrmBasedDispatchableRepository implements
    HasWorkflowStateRepositoryInterface
{
}
