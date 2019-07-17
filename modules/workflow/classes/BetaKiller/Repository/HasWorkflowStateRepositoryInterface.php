<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Workflow\HasWorkflowStateModelInterface;

/**
 * Interface HasWorkflowStateRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method HasWorkflowStateModelInterface[] getAll()
 * @method save(HasWorkflowStateModelInterface $entity)
 */
interface HasWorkflowStateRepositoryInterface extends RepositoryInterface
{

}
