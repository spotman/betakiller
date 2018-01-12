<?php
namespace BetaKiller\Acl;

use BetaKiller\Model\AclResource;
use Spotman\Acl\AclInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;

class AclResourcesCollector implements AclResourcesCollectorInterface
{
    /**
     * @var AclResource
     */
    private $resourceModel;

    /**
     * AclResourcesCollector constructor.
     *
     * @param AclResource $resourceModel
     */
    public function __construct(AclResource $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * Collect resources from external source and add them to acl via protected methods addResource
     *
     * @param \Spotman\Acl\AclInterface $acl
     */
    public function collectResources(AclInterface $acl)
    {
        $resources = $this->resourceModel->getAllResources();

        foreach ($resources as $resource) {
            $acl->addResource($resource->getCodename(), $resource->getParentResourceCodename());
        }
    }
}
