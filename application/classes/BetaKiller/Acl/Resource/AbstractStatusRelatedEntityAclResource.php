<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Status\StatusModelInterface;
use BetaKiller\Status\StatusRelatedModelInterface;

abstract class AbstractStatusRelatedEntityAclResource extends AbstractEntityRelatedAclResource implements StatusRelatedEntityAclResourceInterface
{
    /**
     * Provides array of roles` names which are allowed to create entities
     *
     * @return string[]
     */
    abstract protected function getCreatePermissionRoles();

    /**
     * Provides array of roles` names which are allowed to browse(list) entities
     * @return string[]
     */
    abstract protected function getListPermissionRoles();

    /**
     * Provides array of roles` names which are allowed to search for entities
     *
     * @return string[]
     */
    abstract protected function getSearchPermissionRoles();

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
            self::CREATE_ACTION => $this->getCreatePermissionRoles(),
            self::LIST_ACTION   => $this->getListPermissionRoles(),
            self::SEARCH_ACTION => $this->getSearchPermissionRoles(),
        ];
    }

    /**
     * @param string $permissionIdentity
     *
     * @return bool
     */
    public function isPermissionAllowed($permissionIdentity)
    {
        // Read/Update/Delete permissions rely on model status permissions
        if (in_array($permissionIdentity, $this->getStatusActionsList())) {
            /** @var StatusRelatedModelInterface $entity */
            $entity = $this->getEntity();
            $status = $entity->get_current_status();

            return $this->isStatusActionAllowed($status, $permissionIdentity);
        }

        // Permissions defined in default access list have default logic
        if ($this->isPermissionDefined($permissionIdentity)) {
            return parent::isPermissionAllowed($permissionIdentity);
        }

        /** @var StatusRelatedModelInterface $entity */
        $entity = $this->getEntity();
        $status = $entity->get_current_status();

        // Other permissions rely on model status transition permissions
        return $this->isTransitionAllowed($status, $permissionIdentity);
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

    /**
     * @return string[]
     */
    public function getStatusActionsList()
    {
        return [
            self::READ_ACTION,
            self::UPDATE_ACTION,
            self::DELETE_ACTION,
        ];
    }

    /**
     * Returns true if this resource needs custom permission collector
     *
     * @return bool
     */
    public function isCustomRulesCollectorUsed()
    {
        return true;
    }
}
