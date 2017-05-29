<?php
namespace BetaKiller\Acl;

use Spotman\Acl\AclInterface;
use Model_AclResource;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;

class AclResourcesCollector implements AclResourcesCollectorInterface
{
    /**
     * @var Model_AclResource
     */
    private $resourceModel;

    /**
     * AclResourcesCollector constructor.
     *
     * @param Model_AclResource $resourceModel
     */
    public function __construct(Model_AclResource $resourceModel)
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
