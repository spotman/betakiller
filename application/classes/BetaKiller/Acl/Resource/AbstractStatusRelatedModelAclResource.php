<?php
namespace BetaKiller\Acl\Resource;

use Spotman\Acl\Resource\CrudPermissionsResource;
use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Status\StatusModelInterface;
use Spotman\Acl\Exception;

abstract class AbstractStatusRelatedModelAclResource extends CrudPermissionsResource implements StatusRelatedModelAclResourceInterface
{
    /**
     * @var \BetaKiller\Status\StatusRelatedModelInterface
     */
    private $statusRelatedModel;

    /**
     * @param \BetaKiller\Status\StatusRelatedModelInterface $model
     */
    public function useStatusRelatedModel(StatusRelatedModelInterface $model)
    {
        $this->statusRelatedModel = $model;
    }

    /**
     * @return string[]
     */
    abstract protected function getCreatePermissionRoles();

    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList()
    {
        return [
            self::PERMISSION_CREATE => $this->getCreatePermissionRoles(),
        ];
    }

    /**
     * @param string $permissionIdentity
     *
     * @return bool
     */
    public function isPermissionAllowed($permissionIdentity)
    {
        $status = $this->getStatusRelatedModel()->get_current_status();

        switch ($permissionIdentity) {
            // Create permission has default logic
            case self::PERMISSION_CREATE:
                return parent::isPermissionAllowed($permissionIdentity);

            // Read/Update/Delete permissions rely on model status permissions
            case self::PERMISSION_READ:
            case self::PERMISSION_UPDATE:
            case self::PERMISSION_DELETE:
                return $this->isStatusActionAllowed($status, $permissionIdentity);

            // Other permissions rely on model status transition permissions
            default:
                return $this->isTransitionAllowed($status, $permissionIdentity);
        }
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $model
     * @param string                                  $action
     *
     * @return bool
     */
    public function isStatusActionAllowed(StatusModelInterface $model, $action)
    {
        $identity = $this->makeStatusPermissionIdentity($model, $action);
        return parent::isPermissionAllowed($identity);
    }

    public function isTransitionAllowed(StatusModelInterface $statusModel, $transitionName)
    {
        $identity = $this->makeTransitionPermissionIdentity($statusModel, $transitionName);
        return parent::isPermissionAllowed($identity);
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $model
     * @param string                                  $action
     *
     * @return string
     */
    public function makeStatusPermissionIdentity(StatusModelInterface $model, $action)
    {
        return $this->makeStatusPermissionIdentityBase($model).'.action.'.$action;
    }

    public function makeTransitionPermissionIdentity(StatusModelInterface $statusModel, $transitionName)
    {
        return $this->makeStatusPermissionIdentityBase($statusModel).'.transition.'.$transitionName;
    }

    private function makeStatusPermissionIdentityBase(StatusModelInterface $model)
    {
        return 'status.'.$model->get_codename();
    }

    public function getStatusActionsList()
    {
        return [
            CrudPermissionsResource::PERMISSION_READ,
            CrudPermissionsResource::PERMISSION_UPDATE,
            CrudPermissionsResource::PERMISSION_DELETE,
        ];
    }

    /**
     * @return \BetaKiller\Status\StatusRelatedModelInterface
     * @throws \Spotman\Acl\Exception
     */
    private function getStatusRelatedModel()
    {
        if (!$this->statusRelatedModel) {
            throw new Exception('Status related model is missing, set it via useStatusRelatedModel() method');
        }

        return $this->statusRelatedModel;
    }
}
