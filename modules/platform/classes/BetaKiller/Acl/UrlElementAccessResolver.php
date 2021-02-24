<?php
declare(strict_types=1);

namespace BetaKiller\Acl;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\EntityLinkedUrlElementInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPrototypeService;
use BetaKiller\Url\Zone\ZoneAccessSpecFactory;
use Spotman\Acl\AclException;
use Spotman\Acl\AclInterface;
use Spotman\Acl\AclUserInterface;
use Spotman\Acl\Resource\ResolvingResourceInterface;
use Spotman\Acl\ResourceInterface;

final class UrlElementAccessResolver implements UrlElementAccessResolverInterface
{
    /**
     * @var \Spotman\Acl\AclInterface
     */
    private $acl;

    /**
     * @var \BetaKiller\Url\Zone\ZoneAccessSpecFactory
     */
    private $specFactory;

    /**
     * @var \BetaKiller\Url\UrlPrototypeService
     */
    private $prototypeService;

    /**
     * UrlElementAccessResolver constructor.
     *
     * @param \Spotman\Acl\AclInterface                  $acl
     * @param \BetaKiller\Url\UrlPrototypeService        $prototypeService
     * @param \BetaKiller\Url\Zone\ZoneAccessSpecFactory $specFactory
     */
    public function __construct(
        AclInterface $acl,
        UrlPrototypeService $prototypeService,
        ZoneAccessSpecFactory $specFactory
    ) {
        $this->acl              = $acl;
        $this->specFactory      = $specFactory;
        $this->prototypeService = $prototypeService;
    }

    public function isAllowed(
        AclUserInterface $user,
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null
    ): bool {
        $zoneSpec     = $this->specFactory->createFromUrlElement($urlElement);
        $zoneAclRules = $zoneSpec->getAclRules();
        $zoneRoles    = $zoneSpec->getRolesNames();

        // Check zone roles if defined
        if ($zoneRoles && !$user->hasAnyOfRolesNames($zoneRoles)) {
            return false;
        }

        // Check zone rules if defined
        if ($zoneAclRules && !$this->checkCustomAclRules($zoneAclRules, $user, $params)) {
            return false;
        }

        $urlElementCustomRules = $urlElement->getAdditionalAclRules();

        // Check UrlElement custom rules (process after zone checks, skip zone-related entities if zone is not allowed)
        if (!$this->checkCustomAclRules($urlElementCustomRules, $user, $params)) {
            return false;
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

        // Check DataSource item access
        if ($urlElement->hasDynamicUrl()) {
            $prototype = $this->prototypeService->createPrototypeFromUrlElement($urlElement);

            if (!$prototype->isRawParameter()) {
                $entityName = $prototype->getDataSourceName();

                // Default is READ, everything else can be defined in "aclRules" section of UrlElement config
                $actionName = CrudlsActionsInterface::ACTION_READ;

                // Use bound action name if UrlElement Entity is used in URL prototype
                if ($urlElement instanceof EntityLinkedUrlElementInterface && $urlElement->getEntityModelName() === $entityName) {
                    $actionName = $urlElement->getEntityActionName();
                }

                if (!$this->checkUrlElementEntityPermissions($urlElement, $params, $entityName, $actionName, $user)) {
                    return false;
                }
            }
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

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param \BetaKiller\Url\UrlElementInterface       $urlElement
     *
     * @return bool
     */
    private function isEntityAllowedInZone(AbstractEntityInterface $entity, UrlElementInterface $urlElement): bool
    {
        $result = $this->specFactory->createFromUrlElement($urlElement)->isEntityAllowed($entity);

        // Entity is allowed if entity spec is not defined
        return $result ?? true;
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
     * @param string $identity
     *
     * @return \Spotman\Acl\Resource\ResolvingResourceInterface
     * @throws \Spotman\Acl\AclException
     */
    private function getResource(string $identity): ResolvingResourceInterface
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
}
