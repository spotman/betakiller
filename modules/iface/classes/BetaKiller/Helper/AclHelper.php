<?php
namespace BetaKiller\Helper;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use Spotman\Acl\Exception;
use Spotman\Acl\Resource\ResolvingResourceInterface;

class AclHelper
{
    /**
     * @Inject
     * @var \Spotman\Acl\AclInterface
     */
    private $acl;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string|null                                   $action
     *
     * @return bool
     */
    public function isEntityActionAllowed(DispatchableEntityInterface $entity, ?string $action = null): bool
    {
        $resource = $this->getEntityAclResource($entity);

        return $resource->isPermissionAllowed($action ?? CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     */
    public function getEntityAclResource(DispatchableEntityInterface $entity): EntityRelatedAclResourceInterface
    {
        $name = $entity->getModelName();

        $resource = $this->getAclResourceFromEntityName($name);
        $resource->setEntity($entity);

        return $resource;
    }

    /**
     * @param $name
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     * @throws \Spotman\Acl\Exception
     */
    public function getAclResourceFromEntityName(string $name): EntityRelatedAclResourceInterface
    {
        $resource = $this->acl->getResource($name);

        if (!($resource instanceof EntityRelatedAclResourceInterface)) {
            throw new Exception('Entity resource [:name] must implement :must', [
                ':name' => $name,
                ':must' => EntityRelatedAclResourceInterface::class,
            ]);
        }

        return $resource;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function isIFaceAllowed(IFaceInterface $iface): bool
    {
        $zoneName   = $iface->getZoneName();
        $entityName = $iface->getEntityModelName();
        $actionName = $iface->getEntityActionName();

        if (!$zoneName) {
            throw new IFaceException('IFace :name needs zone to be configured', [
                ':name' => $iface->getCodename(),
            ]);
        }

        $customRules   = $iface->getAdditionalAclRules();
        $entityDefined = $entityName && $actionName;

        // Check custom rules first
        if ($customRules && !$this->checkCustomRules($customRules)) {
            return false;
        }

        // Check entity access second (if defined)
        if ($entityDefined) {
            $resource = $this->getAclResourceFromEntityName($entityName);

            return $resource->isPermissionAllowed($actionName);
        }

        // Allow public access to public zone by default if nor entity or custom rules were not defined
        if ($zoneName === IFaceZone::PUBLIC_ZONE) {
            return true;
        }

        // Other zones must define entity/action or custom rules to protect itself
        if (!($entityDefined || $customRules)) {
            throw new IFaceException('IFace :name must have linked entity or custom ACL rules to protect itself', [
                ':name' => $iface->getCodename(),
            ]);
        }

        // All checks passed
        return true;
    }

    /**
     * @param $identity
     *
     * @return \Spotman\Acl\Resource\ResolvingResourceInterface
     * @throws \Spotman\Acl\Exception
     */
    public function getResource(string $identity): ResolvingResourceInterface
    {
        $resource = $this->acl->getResource($identity);

        if (!($resource instanceof ResolvingResourceInterface)) {
            throw new Exception('Resource :name must implement :must', [
                ':name' => $resource->getResourceId(),
                ':must' => ResolvingResourceInterface::class,
            ]);
        }

        return $resource;
    }

    public function forceAuthorizationIfNeeded(IFaceInterface $iface): void
    {
        // Entering to admin and personal zones requires authorized user
        if ($iface->getZoneName() !== IFaceZone::PUBLIC_ZONE && $this->user->isGuest()) {
            $this->user->forceAuthorization();
        }
    }

    /**
     * @param string[] $rules
     *
     * @return bool
     */
    private function checkCustomRules(array $rules): bool
    {
        foreach ($rules as $value) {
            list($resourceIdentity, $permissionIdentity) = explode('.', $value, 2);

            $resource = $this->getResource($resourceIdentity);

            if (!$resource->isPermissionAllowed($permissionIdentity)) {
                return false;
            }
        }

        return true;
    }
}
