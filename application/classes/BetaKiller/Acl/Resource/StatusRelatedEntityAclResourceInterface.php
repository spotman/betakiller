<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Status\StatusModelInterface;

interface StatusRelatedEntityAclResourceInterface extends EntityRelatedAclResourceInterface
{
    /**
     * @return string[]
     */
    public function getStatusActionsList();

    /**
     * @param \BetaKiller\Status\StatusModelInterface $model
     * @param string                                  $action
     *
     * @return bool
     */
    public function isStatusActionAllowed(StatusModelInterface $model, $action);

    /**
     * @param \BetaKiller\Status\StatusModelInterface $statusModel
     * @param string                                  $transitionName
     *
     * @return bool
     */
    public function isTransitionAllowed(StatusModelInterface $statusModel, $transitionName);

    /**
     * @param \BetaKiller\Status\StatusModelInterface $model
     * @param string                                  $action
     *
     * @return string
     */
    public function makeStatusPermissionIdentity(StatusModelInterface $model, $action);

    /**
     * @param \BetaKiller\Status\StatusModelInterface $statusModel
     * @param string                                  $transitionName
     *
     * @return string
     */
    public function makeTransitionPermissionIdentity(StatusModelInterface $statusModel, $transitionName);
}
