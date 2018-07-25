<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\GuestUser;
use BetaKiller\Model\HasAdminZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPersonalZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPreviewZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPublicZoneAccessSpecificationInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\Zone\ZoneAccessSpecFactory;
use BetaKiller\Url\Zone\ZoneAccessSpecInterface;
use BetaKiller\Url\ZoneInterface;
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
     * @var \BetaKiller\Url\Zone\ZoneAccessSpecFactory
     */
    private $specFactory;

    /**
     * AclHelper constructor.
     *
     * @param \Spotman\Acl\AclInterface                  $acl
     * @param \BetaKiller\Url\Zone\ZoneAccessSpecFactory $specFactory
     * @param \BetaKiller\Model\UserInterface            $user
     */
    public function __construct(AclInterface $acl, ZoneAccessSpecFactory $specFactory, UserInterface $user)
    {
        $this->acl         = $acl;
        $this->user        = $user;
        $this->specFactory = $specFactory;
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
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param null|\Spotman\Acl\AclUserInterface                   $user
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    public function isUrlElementAllowed(
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null,
        ?AclUserInterface $user = null
    ): bool {
        $zoneSpec     = $this->getUrlElementZoneAccessSpec($urlElement);
        $zoneAclRules = $zoneSpec->getAclRules();
        $zoneRoles    = $zoneSpec->getRolesNames();

        // Force guest user in zones without auth (so every public iface must be visible for guest users)
        if (!$zoneSpec->isAuthRequired()) {
            // Public zones need GuestUser to check access)
            $user = new GuestUser;
        }

        // Use current user as default one
        if (!$user) {
            $user = $this->user;
        }

        // Check zone roles if defined, it`s fast
        if ($zoneRoles && !$user->hasAnyOfRolesNames($zoneRoles)) {
            return false;
        }

        // Check zone rules if defined
        if ($zoneAclRules && !$this->checkCustomAclRules($zoneAclRules, $user)) {
            return false;
        }

        $urlElementCustomRules = $urlElement->getAdditionalAclRules();

        // Check UrlElement custom rules
        if (!$this->checkCustomAclRules($urlElementCustomRules, $user)) {
            return false;
        }

        $entityName    = $urlElement->getEntityModelName();
        $actionName    = $urlElement->getEntityActionName();
        $entityDefined = $entityName && $actionName;

        // Check entity access (if defined)
        if ($entityDefined) {
            $resource = $this->getAclResourceFromEntityName($entityName);

            // Copy entity from UrlContainer if required
            if ($resource->isEntityRequiredForAction($actionName)) {
                if (!$params) {
                    throw new Exception('UrlContainer is required for action :action', [
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
                if (!$this->isEntityAllowedInZone($entityInstance, $urlElement)) {
                    return false;
                }

                $resource->setEntity($entityInstance);
            }

            return $this->isPermissionAllowed($resource, $actionName, $user);
        }

        // Allow access to non-protected zones by default if nor entity or custom rules were not defined
        if (!$zoneSpec->isProtectionNeeded()) {
            return true;
        }

        // Other zones must define entity/action or custom rules to protect itself
        if (!($zoneRoles || $zoneAclRules || $entityDefined || $urlElementCustomRules)) {
            throw new IFaceException('UrlElement :name must have linked entity or custom ACL rules to protect itself', [
                ':name' => $urlElement->getCodename(),
            ]);
        }

        // All checks passed
        return true;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface                     $iface
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param null|\Spotman\Acl\AclUserInterface                   $user
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
            case ZoneInterface::PUBLIC:
                return $entity instanceof HasPublicZoneAccessSpecificationInterface
                    ? $entity->isPublicZoneAccessAllowed()
                    : null;

            case ZoneInterface::ADMIN:
                return $entity instanceof HasAdminZoneAccessSpecificationInterface
                    ? $entity->isAdminZoneAccessAllowed()
                    : null;

            case ZoneInterface::PERSONAL:
                return $entity instanceof HasPersonalZoneAccessSpecificationInterface
                    ? $entity->isPersonalZoneAccessAllowed()
                    : null;

            case ZoneInterface::PREVIEW:
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
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    public function forceAuthorizationIfNeeded(UrlElementInterface $urlElement): void
    {
        $zoneSpec = $this->getUrlElementZoneAccessSpec($urlElement);

        // User authorization is required for entering protected zones
        if ($zoneSpec->isAuthRequired() && $this->user->isGuest()) {
            $this->user->forceAuthorization();
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return \BetaKiller\Url\Zone\ZoneAccessSpecInterface
     */
    private function getUrlElementZoneAccessSpec(UrlElementInterface $urlElement): ZoneAccessSpecInterface
    {
        return $this->specFactory->create($urlElement->getZoneName());
    }

    /**
     * @param string[]                           $rules
     * @param null|\Spotman\Acl\AclUserInterface $user
     *
     * @return bool
     * @throws \Spotman\Acl\Exception
     */
    private function checkCustomAclRules(array $rules, ?AclUserInterface $user = null): bool
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
