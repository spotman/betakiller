<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\BreadsActionsInterface;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\Url\DispatchableEntityInterface;
use Spotman\Acl\Exception;
use Spotman\Acl\Resource\ResolvingResourceInterface;

class AclHelper
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\Url\UrlDataSourceFactory
     */
    private $dataSourceFactory;

    /**
     * @Inject
     * @var \BetaKiller\Factory\OrmFactory
     */
    private $ormFactory;

    /**
     * @Inject
     * @var \Spotman\Acl\AclInterface
     */
    private $acl;


    public function isEntityActionAllowed(DispatchableEntityInterface $entity, $action = null)
    {
        if (!$action) {
            $action = BreadsActionsInterface::READ;
        }

        $resource = $this->getEntityAclResource($entity);

        return $this->acl->isAllowed($resource, $action);
    }

    public function getEntityAclResource(DispatchableEntityInterface $entity)
    {
        $name = $entity->getModelName();

        return $this->getAclResourceFromEntityName($name);
    }

    private function getAclResourceFromEntityName($name)
    {
        $resource = $this->acl->getResource($name);

        if (!($resource instanceof ResolvingResourceInterface)) {
            throw new Exception('Entity :name must be linked to resolvable acl resource', [
                ':name' => $name,
            ]);
        }

        return $resource;
    }

    public function isIFaceAllowed(IFaceInterface $iface)
    {
        $entityName = $iface->getEntityModelName();
        $actionName = $iface->getEntityActionName();

        $resource = $this->getAclResourceFromEntityName($entityName);

        return $this->acl->isAllowed($resource, $actionName);
    }

}
