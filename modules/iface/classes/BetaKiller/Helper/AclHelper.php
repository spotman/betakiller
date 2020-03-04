<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\EntityLinkedUrlElementInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPrototypeService;
use BetaKiller\Url\Zone\ZoneAccessSpecFactory;
use BetaKiller\Url\Zone\ZoneAccessSpecInterface;
use Spotman\Acl\AclException;
use Spotman\Acl\AclInterface;
use Spotman\Acl\AclUserInterface;
use Spotman\Acl\Resource\ResolvingResourceInterface;
use Spotman\Acl\ResourceInterface;

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
     * @var \BetaKiller\Url\UrlPrototypeService
     */
    private $prototypeService;

    /**
     * AclHelper constructor.
     *
     * @param \Spotman\Acl\AclInterface                  $acl
     * @param \BetaKiller\Url\UrlPrototypeService        $prototypeService
     * @param \BetaKiller\Url\Zone\ZoneAccessSpecFactory $specFactory
     * @param \BetaKiller\Factory\GuestUserFactory       $guestFactory
     */
    public function __construct(
        AclInterface $acl,
        UrlPrototypeService $prototypeService,
        ZoneAccessSpecFactory $specFactory,
        GuestUserFactory $guestFactory
    ) {
        $this->acl              = $acl;
        $this->specFactory      = $specFactory;
        $this->guestFactory     = $guestFactory;
        $this->prototypeService = $prototypeService;
    }

    /**
     * @param \BetaKiller\Model\UserInterface           $user
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param string                                    $action
     *
     * @return bool
     * @throws \Spotman\Acl\AclException
     */
    public function isEntityPermissionAllowed(
        UserInterface $user,
        AbstractEntityInterface $entity,
        string $action
    ): bool {
        $resource = $this->getEntityAclResource($entity);

        $resource->setEntity($entity);

        return $this->isPermissionAllowed($user, $resource, $action);
    }

    public function getGuestUser(): GuestUserInterface
    {
        return $this->guestFactory->create();
    }

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     * @throws \Spotman\Acl\AclException
     */
    public function getEntityAclResource(AbstractEntityInterface $entity): EntityRelatedAclResourceInterface
    {
        $name = $entity::getModelName();

        $resource = $this->getAclResourceForEntityName($name);
        $resource->setEntity($entity);

        return $resource;
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     * @throws \Spotman\Acl\AclException
     */
    private function getAclResourceForEntityName(string $name): EntityRelatedAclResourceInterface
    {
        $resource = $this->acl->getResource($name);

        if (!($resource instanceof EntityRelatedAclResourceInterface)) {
            throw new AclException('Entity resource [:name] must implement :must', [
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
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \Spotman\Acl\AclException
     */
    public function isUrlElementAllowed(
        AclUserInterface $user,
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null
    ): bool {
        $urlElementCustomRules = $urlElement->getAdditionalAclRules();

        // Check UrlElement custom rules
        if (!$this->checkCustomAclRules($urlElementCustomRules, $user, $params)) {
            return false;
        }

        // Check DataSource item access
        if ($urlElement->hasDynamicUrl()) {
            $prototype = $this->prototypeService->createPrototypeFromUrlElement($urlElement);

            if (!$prototype->isRawParameter()) {
                $entityName = $prototype->getDataSourceName();

                // Default is READ, everything else can be defined in "aclRules" section of UrlElement config
                $actionName = CrudlsActionsInterface::ACTION_READ;

                if (!$this->checkUrlElementEntityPermissions($urlElement, $params, $entityName, $actionName, $user)) {
                    return false;
                }
            }
        }

        // Custom ACL rules => protection defined
        $protectionDefined = (bool)$urlElementCustomRules;

        if ($urlElement instanceof EntityLinkedUrlElementInterface) {
            $entityName = $urlElement->getEntityModelName();
            $actionName = $urlElement->getEntityActionName();

            // Check entity access (if defined)
            if ($entityName && $actionName) {
                if (!$this->checkUrlElementEntityPermissions($urlElement, $params, $entityName, $actionName, $user)) {
                    return false;
                }

                // Entity action defined
                $protectionDefined = true;
            }
        }

        $zoneSpec     = $this->getZoneAccessSpec($urlElement);
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
        if ($zoneAclRules && !$this->checkCustomAclRules($zoneAclRules, $user, $params)) {
            return false;
        }

        // Allow access to non-protected zones by default if nor entity or custom rules were not defined
        if (!$zoneSpec->isProtectionNeeded()) {
            return true;
        }

        if ($zoneRoles || $zoneAclRules) {
            $protectionDefined = true;
        }

        // IFaces and Actions from protected zones must define entity/action or custom rules to protect itself
        if (!$protectionDefined) {
            throw new UrlElementException(
                'UrlElement :name must have linked entity or custom ACL rules to protect itself', [
                ':name' => $urlElement->getCodename(),
            ]);
        }

        // All checks passed
        return true;
    }

    private function checkUrlElementEntityPermissions(
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params,
        string $entityName,
        string $actionName,
        AclUserInterface $user
    ): bool {
        $resource = $this->getAclResourceForEntityName($entityName);

        $entityInstance = $this->fetchEntityIfRequired($resource, $actionName, $params);

        // Inject Entity if required
        if ($entityInstance) {
            $resource->setEntity($entityInstance);
        }

        // Check zone access
        if ($entityInstance && !$this->isEntityAllowedInZone($entityInstance, $urlElement)) {
            return false;
        }

        return $this->isPermissionAllowed($user, $resource, $actionName);
    }

    private function fetchEntityIfRequired(
        ResourceInterface $resource,
        string $actionName,
        ?UrlContainerInterface $params
    ): ?AbstractEntityInterface {
        if (!$resource instanceof EntityRelatedAclResourceInterface) {
            return null;
        }

        if (!$resource->isEntityRequiredForAction($actionName)) {
            return null;
        }

        // Fetch entity from UrlContainer if required
        if (!$params) {
            throw new AclException('UrlContainer is required for action ":action"', [
                ':action' => $actionName,
            ]);
        }

        $entityName = $resource->getResourceId();

        $entityInstance = $params->getEntity($entityName);

        if (!$entityInstance) {
            throw new AclException('Entity instance ":entity" is absent for action ":action"', [
                ':entity' => $entityName,
                ':action' => $actionName,
            ]);
        }

        return $entityInstance;
    }

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param \BetaKiller\Url\UrlElementInterface       $urlElement
     *
     * @return bool
     */
    private function isEntityAllowedInZone(AbstractEntityInterface $entity, UrlElementInterface $urlElement): bool
    {
        $result = $this->getZoneAccessSpec($urlElement)->isEntityAllowed($entity);

        // Entity is allowed if entity spec is not defined
        return $result ?? true;
    }

    /**
     * @param string $identity
     *
     * @return \Spotman\Acl\Resource\ResolvingResourceInterface
     * @throws \Spotman\Acl\AclException
     */
    public function getResource(string $identity): ResolvingResourceInterface
    {
        $resource = $this->acl->getResource($identity);

        if (!($resource instanceof ResolvingResourceInterface)) {
            throw new AclException('Resource :name must implement :must', [
                ':name' => $resource->getResourceId(),
                ':must' => ResolvingResourceInterface::class,
            ]);
        }

        return $resource;
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

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    public function forceAuthorizationIfNeeded(UrlElementInterface $urlElement, UserInterface $user): void
    {
        $zoneSpec = $this->getZoneAccessSpec($urlElement);

        // User authorization is required for entering protected zones
        if ($zoneSpec->isAuthRequired() && $user->isGuest()) {
            $user->forceAuthorization();
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return \BetaKiller\Url\Zone\ZoneAccessSpecInterface
     */
    private function getZoneAccessSpec(UrlElementInterface $urlElement): ZoneAccessSpecInterface
    {
        return $this->specFactory->createFromUrlElement($urlElement);
    }

    /**
     * @param string[]                                             $rules
     * @param \Spotman\Acl\AclUserInterface                        $user
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \Spotman\Acl\AclException
     */
    private function checkCustomAclRules(array $rules, AclUserInterface $user, ?UrlContainerInterface $params): bool
    {
        // No rules = allow access
        if (!$rules) {
            return true;
        }

        foreach ($rules as $value) {
            [$resourceIdentity, $permissionIdentity] = explode('.', $value, 2);

            $resource = $this->getResource($resourceIdentity);

            $entityInstance = $this->fetchEntityIfRequired($resource, $permissionIdentity, $params);

            if ($entityInstance && $resource instanceof EntityRelatedAclResourceInterface) {
                $resource->setEntity($entityInstance);
            }

            if (!$this->isPermissionAllowed($user, $resource, $permissionIdentity)) {
                return false;
            }
        }

        return true;
    }
}
