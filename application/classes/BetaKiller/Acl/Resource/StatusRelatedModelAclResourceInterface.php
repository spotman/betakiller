<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Status\StatusModelInterface;
use Spotman\Acl\Resource\CrudPermissionsResourceInterface;
use BetaKiller\Status\StatusRelatedModelInterface;

interface StatusRelatedModelAclResourceInterface extends CrudPermissionsResourceInterface
{
    /**
     * @param \BetaKiller\Status\StatusRelatedModelInterface $model
     */
    public function useStatusRelatedModel(StatusRelatedModelInterface $model);

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

    public function isTransitionAllowed(StatusModelInterface $statusModel, $transitionName);

    /**
     * @param \BetaKiller\Status\StatusModelInterface $model
     * @param string                                  $action
     *
     * @return string
     */
    public function makeStatusPermissionIdentity(StatusModelInterface $model, $action);

    public function makeTransitionPermissionIdentity(StatusModelInterface $statusModel, $transitionName);
}
