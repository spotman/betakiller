<?php
namespace BetaKiller\Helper;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\GuestUser;
use BetaKiller\Model\HasAdminZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPersonalZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPreviewZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPublicZoneAccessSpecificationInterface;
use BetaKiller\Model\UrlElementZone;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use Spotman\Acl\AccessResolver\UserAccessResolver;
use Spotman\Acl\AclInterface;
use Spotman\Acl\AclUserInterface;
use Spotman\Acl\Exception;
use Spotman\Acl\Resource\ResolvingResourceInterface;

class AclHelper
{
    /**
     * @var \Spotman\Acl\AclInterface
     */
    private $acl;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * AclHelper constructor.
     *
     * @param \Spotman\Acl\AclInterface       $acl
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(AclInterface $acl, UserInterface $user)
    {
        $this->acl  = $acl;
        $this->user = $user;
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string|null                                   $action
     *
     * @return bool
     * @throws \Spotman\Acl\Exception
     */
    public function isEntityActionAllowed(DispatchableEntityInterface $entity, ?string $action = null): bool
    {
        $resource = $this->getEntityAclResource($entity);

        return $this->isPermissionAllowed($resource, $action ?? CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     * @throws \Spotman\Acl\Exception
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
     * @param \BetaKiller\Url\UrlElementInterface        $model
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     * @param null|\Spotman\Acl\AclUserInterface         $user
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    public function isUrlElementAllowed(
        UrlElementInterface $model,
        ?UrlContainerInterface $params = null,
        ?AclUserInterface $user = null
    ): bool {
        $zoneName   = $model->getZoneName();
        $entityName = $model->getEntityModelName();
        $actionName = $model->getEntityActionName();

        if (!$zoneName) {
            throw new IFaceException('IFace :name needs zone to be configured', [
                ':name' => $model->getCodename(),
            ]);
        }

        // Force check for guest role in public zone (so every public iface must be visible for guest users)
        if ($zoneName === UrlElementZone::PUBLIC_ZONE) {
            // Public zone needs GuestUser to check access)
            $user = new GuestUser;
        }

        // Use current user as default one
        if (!$user) {
            $user = $this->user;
        }

        $customRules   = $model->getAdditionalAclRules();
        $entityDefined = $entityName && $actionName;

        // Force check for admin panel is enabled
        if ($zoneName === UrlElementZone::ADMIN_ZONE) {
            $customRules[] = AdminResource::SHORTCUT;
        }

        // Check custom rules first
        if (!$this->checkCustomRules($customRules, $user)) {
            return false;
        }

        // Check entity access (if defined)
        if ($entityDefined) {
            $resource = $this->getAclResourceFromEntityName($entityName);

            // Copy entity from UrlContainer if required
            if ($resource->isEntityRequiredForAction($actionName)) {
                if (!$params) {
                    throw new Exception('UrlContainer are required for action :action', [
                        ':action' => $actionName,
                    ]);
                }

                $entityInstance = $params->getEntity($entityName);

                if (!$entityInstance) {
                    throw new Exception('Entity instance :entity is absent in UrlContainer for action :action', [
                        ':entity' => $entityName,
                        ':action' => $actionName,
                    ]);
                }

                // Check zone access
                if (!$this->isEntityAllowedInZone($entityInstance, $model)) {
                    return false;
                }

                $resource->setEntity($entityInstance);
            }

            return $this->isPermissionAllowed($resource, $actionName, $user);
        }

        // Allow access to public/personal zone by default if nor entity or custom rules were not defined
        if (\in_array($zoneName, [UrlElementZone::PUBLIC_ZONE, UrlElementZone::PERSONAL_ZONE], true)) {
            return true;
        }

        // Other zones must define entity/action or custom rules to protect itself
        if (!($entityDefined || $customRules)) {
            throw new IFaceException('IFace :name must have linked entity or custom ACL rules to protect itself', [
                ':name' => $model->getCodename(),
            ]);
        }

        // All checks passed
        return true;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface           $iface
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     * @param null|\Spotman\Acl\AclUserInterface         $user
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    public function isIFaceAllowed(
        IFaceInterface $iface,
        ?UrlContainerInterface $params = null,
        ?AclUserInterface $user = null
    ): bool {
        return $this->isUrlElementAllowed($iface->getModel(), $params, $user);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param \BetaKiller\Url\UrlElementInterface           $model
     *
     * @return bool
     * @throws \Spotman\Acl\Exception
     */
    private function isEntityAllowedInZone(DispatchableEntityInterface $entity, UrlElementInterface $model): bool
    {
        $spec = $this->getEntityZoneAccessSpecification($entity, $model);

        // Entity allowed if no spec defined
        return $spec ?? true;
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param \BetaKiller\Url\UrlElementInterface           $model
     *
     * @return bool|null
     * @throws \Spotman\Acl\Exception
     */
    private function getEntityZoneAccessSpecification(
        DispatchableEntityInterface $entity,
        UrlElementInterface $model
    ): ?bool {
        $zoneName = $model->getZoneName();

        switch ($zoneName) {
            case UrlElementZone::PUBLIC_ZONE:
                return $entity instanceof HasPublicZoneAccessSpecificationInterface
                    ? $entity->isPublicZoneAccessAllowed()
                    : null;

            case UrlElementZone::ADMIN_ZONE:
                return $entity instanceof HasAdminZoneAccessSpecificationInterface
                    ? $entity->isAdminZoneAccessAllowed()
                    : null;

            case UrlElementZone::PERSONAL_ZONE:
                return $entity instanceof HasPersonalZoneAccessSpecificationInterface
                    ? $entity->isPersonalZoneAccessAllowed()
                    : null;

            case UrlElementZone::PREVIEW_ZONE:
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

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    public function forceAuthorizationIfNeeded(UrlElementInterface $model): void
    {
        // Entering to admin and personal zones requires authorized user
        if ($model->getZoneName() !== UrlElementZone::PUBLIC_ZONE && $this->user->isGuest()) {
            $this->user->forceAuthorization();
        }
    }

    /**
     * @param string[]                           $rules
     * @param null|\Spotman\Acl\AclUserInterface $user
     *
     * @return bool
     * @throws \Spotman\Acl\Exception
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

    /**
     * @param \Spotman\Acl\Resource\ResolvingResourceInterface $resource
     * @param string                                           $permission
     * @param null|\Spotman\Acl\AclUserInterface               $user
     *
     * @return bool
     */
    private function isPermissionAllowed(
        ResolvingResourceInterface $resource,
        string $permission,
        ?AclUserInterface $user = null
    ): bool {
        if ($user) {
            $resource->useResolver(new UserAccessResolver($this->acl, $user));
        }

        return $resource->isPermissionAllowed($permission);
    }
}
