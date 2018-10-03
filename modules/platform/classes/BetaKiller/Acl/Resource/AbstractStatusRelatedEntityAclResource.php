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
    abstract protected function getCreatePermissionRoles(): array;

    /**
     * Provides array of roles` names which are allowed to browse(list) entities
     * @return string[]
     */
    abstract protected function getListPermissionRoles(): array;

    /**
     * Provides array of roles` names which are allowed to search for entities
     *
     * @return string[]
     */
    abstract protected function getSearchPermissionRoles(): array;

    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList(): array
    {
        return [
            self::ACTION_CREATE => $this->getCreatePermissionRoles(),
            self::ACTION_LIST   => $this->getListPermissionRoles(),
            self::ACTION_SEARCH => $this->getSearchPermissionRoles(),
        ];
    }

    /**
     * @param string $permissionIdentity
     *
     * @return bool
     */
    public function isPermissionAllowed(string $permissionIdentity): bool
    {
        // Read/Update/Delete permissions rely on model status permissions
        if (\in_array($permissionIdentity, $this->getStatusActionsList(), true)) {
            /** @var StatusRelatedModelInterface $entity */
            $entity = $this->getEntity();
            $status = $entity->getCurrentStatus();

            return $this->isStatusActionAllowed($status, $permissionIdentity);
        }

        // Permissions defined in default access list have default logic
        if ($this->isPermissionDefined($permissionIdentity)) {
            return parent::isPermissionAllowed($permissionIdentity);
        }

        /** @var StatusRelatedModelInterface $entity */
        $entity = $this->getEntity();
        $status = $entity->getCurrentStatus();

        // Other permissions rely on model status transition permissions
        return $this->isTransitionAllowed($status, $permissionIdentity);
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $model
     * @param string                                  $action
     *
     * @return bool
     */
    public function isStatusActionAllowed(StatusModelInterface $model, $action): bool
    {
        $identity = $this->makeStatusPermissionIdentity($model, $action);

        return parent::isPermissionAllowed($identity);
    }

    public function isTransitionAllowed(StatusModelInterface $statusModel, $transitionName): bool
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
    public function makeStatusPermissionIdentity(StatusModelInterface $model, string $action): string
    {
        return $this->makeStatusPermissionIdentityBase($model).'.action.'.$action;
    }

    public function makeTransitionPermissionIdentity(StatusModelInterface $statusModel, string $transitionName): string
    {
        return $this->makeStatusPermissionIdentityBase($statusModel).'.transition.'.$transitionName;
    }

    private function makeStatusPermissionIdentityBase(StatusModelInterface $model): string
    {
        return 'status.'.$model->getCodename();
    }

    /**
     * @return string[]
     */
    public function getStatusActionsList(): array
    {
        return [
            self::ACTION_READ,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
        ];
    }

    /**
     * Returns true if this resource needs custom permission collector
     *
     * @return bool
     */
    public function isCustomRulesCollectorUsed(): bool
    {
        return true;
    }
}
