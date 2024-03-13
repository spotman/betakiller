<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ContentPostState;
use BetaKiller\Workflow\WorkflowStateInterface;

/**
 * @method WorkflowStateInterface[] getAll()
 * @method  save(WorkflowStateInterface $entity)
 */
class ContentPostStateRepository extends AbstractWorkflowStateOrmRepository implements
    ContentPostStateRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return ContentPostState::COL_CODENAME;
    }
}
