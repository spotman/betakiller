<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\HasAdminZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPersonalZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPreviewZoneAccessSpecificationInterface;
use BetaKiller\Model\HasPublicZoneAccessSpecificationInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\Zone\ZoneAccessSpecFactory;
use BetaKiller\Url\Zone\ZoneAccessSpecInterface;
use BetaKiller\Url\ZoneInterface;
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
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private $guestFactory;

    /**
     * @var \BetaKiller\Url\Zone\ZoneAccessSpecFactory
     */
    private $specFactory;

    /**
     * AclHelper constructor.
     *
     * @param \Spotman\Acl\AclInterface                  $acl
     * @param \BetaKiller\Url\Zone\ZoneAccessSpecFactory $specFactory
     * @param \BetaKiller\Factory\GuestUserFactory       $guestFactory
     */
    public function __construct(
        AclInterface $acl,
        ZoneAccessSpecFactory $specFactory,
        GuestUserFactory $guestFactory
    ) {
        $this->acl          = $acl;
        $this->specFactory  = $specFactory;
        $this->guestFactory = $guestFactory;
    }

    /**
     * @param \BetaKiller\Model\UserInterface               $user
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string|null                                   $action
     *
     * @return bool
     * @throws \Spotman\Acl\Exception
     */
    public function isEntityActionAllowed(
        UserInterface $user,
        DispatchableEntityInterface $entity,
        ?string $action = null
    ): bool {
        $resource = $this->getEntityAclResource($entity);
        $action   = $action ?? CrudlsActionsInterface::ACTION_READ;

        return $this->isPermissionAllowed($user, $resource, $action);
    }

    public function getGuestUser(): GuestUserInterface
    {
        return $this->guestFactory->create();
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
     * @param \Spotman\Acl\AclUserInterface                        $user
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \Spotman\Acl\Exception
     */
    public function isUrlElementAllowed(
        AclUserInterface $user,
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null
    ): bool {
        $urlElementCustomRules = $urlElement->getAdditionalAclRules();

        // Check UrlElement custom rules
        if (!$this->checkCustomAclRules($urlElementCustomRules, $user)) {
            return false;
        }

        if ($urlElement instanceof IFaceModelInterface) {
            $zoneSpec     = $this->getIFaceZoneAccessSpec($urlElement);
            $zoneAclRules = $zoneSpec->getAclRules();
            $zoneRoles    = $zoneSpec->getRolesNames();

            // Force guest user in zones without auth (so every public iface must be visible for guest users)
            if (!$zoneSpec->isAuthRequired()) {
                // TODO Extract this check to console task executed before deployment
                // Public zones need GuestUser to check access)
                $user = $this->guestFactory->create();
            }

            // Check zone roles if defined
            if ($zoneRoles && !$user->hasAnyOfRolesNames($zoneRoles)) {
                return false;
            }

            // Check zone rules if defined
            if ($zoneAclRules && !$this->checkCustomAclRules($zoneAclRules, $user)) {
                return false;
            }

            $entityName = $urlElement->getEntityModelName();
            $actionName = $urlElement->getEntityActionName();

            $entityActionDefined = $entityName && $actionName;

            // Check entity access (if defined)
            if ($entityActionDefined) {
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

                if (!$this->isPermissionAllowed($user, $resource, $actionName)) {
                    return false;
                }
            }

            // Allow access to non-protected zones by default if nor entity or custom rules were not defined
            if (!$zoneSpec->isProtectionNeeded()) {
                return true;
            }

            // IFaces from protected zones must define entity/action or custom rules to protect itself
            if (!($zoneRoles || $zoneAclRules || $urlElementCustomRules || $entityActionDefined)) {
                throw new UrlElementException('UrlElement :name must have linked entity or custom ACL rules to protect itself',
                    [
                        ':name' => $urlElement->getCodename(),
                    ]);
            }
        }

        // All checks passed
        return true;
    }

    /**
     * @param null|\Spotman\Acl\AclUserInterface                   $user
     *
     * @param \BetaKiller\IFace\IFaceInterface                     $iface
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \Spotman\Acl\Exception
     */
    public function isIFaceAllowed(
        AclUserInterface $user,
        IFaceInterface $iface,
        ?UrlContainerInterface $params = null
    ): bool {
        return $this->isUrlElementAllowed($user, $iface->getModel(), $params);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param \BetaKiller\Url\IFaceModelInterface           $model
     *
     * @return bool
     * @throws \Spotman\Acl\Exception
     */
    private function isEntityAllowedInZone(DispatchableEntityInterface $entity, IFaceModelInterface $model): bool
    {
        $spec = $this->getEntityZoneAccessSpecification($entity, $model);

        // Entity allowed if no spec defined
        return $spec ?? true;
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param \BetaKiller\Url\IFaceModelInterface           $model
     *
     * @return bool|null
     * @throws \Spotman\Acl\Exception
     */
    private function getEntityZoneAccessSpecification(
        DispatchableEntityInterface $entity,
        IFaceModelInterface $model
    ): ?bool {
        $zoneName = $model->getZoneName();

        // TODO refactoring to dynamic zones list (EntityZoneAccessSpec + factory by zone name with cached instances)
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
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    public function forceAuthorizationIfNeeded(UrlElementInterface $urlElement, UserInterface $user): void
    {
        // Only IFaces have zone definition
        if (!$urlElement instanceof IFaceModelInterface) {
            return;
        }

        $zoneSpec = $this->getIFaceZoneAccessSpec($urlElement);

        // User authorization is required for entering protected zones
        if ($zoneSpec->isAuthRequired() && $user->isGuest()) {
            $user->forceAuthorization();
        }
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $urlElement
     *
     * @return \BetaKiller\Url\Zone\ZoneAccessSpecInterface
     */
    private function getIFaceZoneAccessSpec(IFaceModelInterface $urlElement): ZoneAccessSpecInterface
    {
        return $this->specFactory->create($urlElement->getZoneName());
    }

    /**
     * @param string[]                      $rules
     * @param \Spotman\Acl\AclUserInterface $user
     *
     * @return bool
     * @throws \Spotman\Acl\Exception
     */
    private function checkCustomAclRules(array $rules, AclUserInterface $user): bool
    {
        // No rules = allow access
        if (!$rules) {
            return true;
        }

        foreach ($rules as $value) {
            list($resourceIdentity, $permissionIdentity) = explode('.', $value, 2);

            $resource = $this->getResource($resourceIdentity);

            if (!$this->isPermissionAllowed($user, $resource, $permissionIdentity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Spotman\Acl\AclUserInterface                    $user
     *
     * @param \Spotman\Acl\Resource\ResolvingResourceInterface $resource
     * @param string                                           $permission
     *
     * @return bool
     */
    private function isPermissionAllowed(
        AclUserInterface $user,
        ResolvingResourceInterface $resource,
        string $permission
    ): bool {
        $this->acl->injectUserResolver($user, $resource);

        return $resource->isPermissionAllowed($permission);
    }
}
