<?php
declare(strict_types=1);

namespace BetaKiller\Acl;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\EntityWithAclSpecInterface;
use Spotman\Acl\AclException;
use Spotman\Acl\AclInterface;
use Spotman\Acl\AclUserInterface;

final class EntityPermissionResolver implements EntityPermissionResolverInterface
{
    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * @var \BetaKiller\Acl\EntityAclSpecFactory
     */
    private EntityAclSpecFactory $aclSpecFactory;

    /**
     * EntityPermissionResolver constructor.
     *
     * @param \Spotman\Acl\AclInterface            $acl
     * @param \BetaKiller\Acl\EntityAclSpecFactory $aclSpecFactory
     */
    public function __construct(AclInterface $acl, EntityAclSpecFactory $aclSpecFactory)
    {
        $this->acl            = $acl;
        $this->aclSpecFactory = $aclSpecFactory;
    }

    /**
     * @inheritDoc
     */
    public function isAllowed(
        AclUserInterface $user,
        AbstractEntityInterface $entity,
        ?string $action = null,
        bool $skipSpecCheck = null
    ): bool {
        $action = $action ?? CrudlsActionsInterface::ACTION_READ;

        $resource = $this->getEntityAclResource($entity);

        $this->acl->injectUserResolver($user, $resource);

        if (!$resource->isPermissionAllowed($action)) {
            return false;
        }

        if ($skipSpecCheck) {
            return true;
        }

        return $this->checkAclSpec($entity, $user);
    }

    private function checkAclSpec(AbstractEntityInterface $entity, AclUserInterface $user): bool
    {
        if (!$entity instanceof EntityWithAclSpecInterface) {
            return true;
        }

        return $this->aclSpecFactory->createFor($entity)->isAllowedTo($entity, $user);
    }

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface
     * @throws \Spotman\Acl\AclException
     */
    private function getEntityAclResource(AbstractEntityInterface $entity): EntityRelatedAclResourceInterface
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
}
