<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Workflow\HasWorkflowStateInterface;

/**
 * Interface HasWorkflowStateRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method HasWorkflowStateInterface[] getAll()
 * @method save(HasWorkflowStateInterface $entity)
 */
interface HasWorkflowStateRepositoryInterface extends RepositoryInterface
{

}
