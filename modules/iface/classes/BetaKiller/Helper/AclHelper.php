<?php
namespace BetaKiller\Helper;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\GuestUser;
use BetaKiller\Model\HasAdminZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPersonalZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPreviewZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPublicZoneAccessSpecificationInterface;
use BetaKiller\Model\IFaceZone;
use Spotman\Acl\AccessResolver\UserAccessResolver;
use Spotman\Acl\AclUserInterface;
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

        return $this->isPermissionAllowed($resource,$action ?? CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     */
    private function getEntityAclResource(DispatchableEntityInterface $entity): EntityRelatedAclResourceInterface
    {
        $name = $entity->getModelName();

        $resource = $this->getAclResourceFromEntityName($name);
        $resource->setEntity($entity);

        return $resource;
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     * @throws \Spotman\Acl\Exception
     */
    private function getAclResourceFromEntityName(string $name): EntityRelatedAclResourceInterface
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
     * @param \BetaKiller\IFace\IFaceInterface                  $iface
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $params
     * @param null|\Spotman\Acl\AclUserInterface                $user
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    public function isIFaceAllowed(IFaceInterface $iface, ?UrlParametersInterface $params = null, ?AclUserInterface $user = null): bool
    {
        $zoneName   = $iface->getZoneName();
        $entityName = $iface->getEntityModelName();
        $actionName = $iface->getEntityActionName();

        if (!$zoneName) {
            throw new IFaceException('IFace :name needs zone to be configured', [
                ':name' => $iface->getCodename(),
            ]);
        }

        if (!$user && $zoneName === IFaceZone::PUBLIC_ZONE) {
            // Public zone needs GuestUser to check access)
            $user = new GuestUser;
        }

        $customRules   = $iface->getAdditionalAclRules();
        $entityDefined = $entityName && $actionName;

        // Check custom rules first
        if (!$this->checkCustomRules($customRules, $user)) {
            return false;
        }

        // Check entity access (if defined)
        if ($entityDefined) {
            $resource = $this->getAclResourceFromEntityName($entityName);

            // Copy entity from UrlParameters if required
            if ($resource->isEntityRequiredForAction($actionName)) {
                if (!$params) {
                    throw new Exception('UrlParameters are required for action :action', [
                        ':action' => $actionName,
                    ]);
                }

                $entityInstance = $params->getEntity($entityName);

                if (!$entityInstance) {
                    throw new Exception('Entity instance :entity is absent in UrlParameters for action :action', [
                        ':entity' => $entityName,
                        ':action' => $actionName,
                    ]);
                }

                // Check zone access
                if (!$this->isEntityAllowedInZone($entityInstance, $iface)) {
                    return false;
                }

                $resource->setEntity($entityInstance);
            }

            return $this->isPermissionAllowed($resource, $actionName, $user);
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

    private function isEntityAllowedInZone(DispatchableEntityInterface $entity, IFaceInterface $iface): bool
    {
        $spec = $this->getEntityZoneAccessSpecification($entity, $iface);

        // Entity allowed if no spec defined
        return $spec ?? true;
    }

    private function getEntityZoneAccessSpecification(DispatchableEntityInterface $entity, IFaceInterface $iface): ?bool
    {
        $zoneName = $iface->getZoneName();

        switch ($zoneName) {
            case IFaceZone::PUBLIC_ZONE:
                return $entity instanceof HasPublicZoneAccessSpecificationInterface
                    ? $entity->isPublicZoneAccessAllowed()
                    : null;

            case IFaceZone::ADMIN_ZONE:
                return $entity instanceof HasAdminZoneAccessSpecificationInterface
                    ? $entity->isAdminZoneAccessAllowed()
                    : null;

            case IFaceZone::PERSONAL_ZONE:
                return $entity instanceof HasPersonalZoneAccessSpecificationInterface
                    ? $entity->isPersonalZoneAccessAllowed()
                    : null;

            case IFaceZone::PREVIEW_ZONE:
                return $entity instanceof HasPreviewZoneAccessSpecificationInterface
                    ? $entity->isPreviewZoneAccessAllowed()
                    : null;

            default:
                throw new Exception('Unknown zone name :value', [':value' => $zoneName]);
        }
    }

    /**
     * @param string $identity
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
    private function checkCustomRules(array $rules, ?AclUserInterface $user = null): bool
    {
        // No rules = allow access
        if (!$rules) {
            return true;
        }

        foreach ($rules as $value) {
            list($resourceIdentity, $permissionIdentity) = explode('.', $value, 2);

            $resource = $this->getResource($resourceIdentity);

            if (!$this->isPermissionAllowed($resource, $permissionIdentity, $user)) {
                return false;
            }
        }

        return true;
    }

    private function isPermissionAllowed(ResolvingResourceInterface $resource, string $permission, ?AclUserInterface $user = null): bool
    {
        if ($user) {
            $resource->useResolver(new UserAccessResolver($this->acl, $user));
        }

        return $resource->isPermissionAllowed($permission);
    }
}
