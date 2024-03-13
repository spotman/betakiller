<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\HasWorkflowStateInterface;

/**
 * Interface HasWorkflowStateRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method HasWorkflowStateInterface[] getAll()
 */
interface HasWorkflowStateRepositoryInterface extends RepositoryInterface
{
    /**
     * @return HasWorkflowStateInterface[]
     */
    public function getAllMissingState(): array;
}
