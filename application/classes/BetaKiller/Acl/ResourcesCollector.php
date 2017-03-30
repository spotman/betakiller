<?php
namespace BetaKiller\Acl;

use Spotman\Acl\Acl;
use Model_AclResource;
use Spotman\Acl\ResourcesCollector\ResourcesCollectorInterface;

class ResourcesCollector implements ResourcesCollectorInterface
{
    /**
     * @var Model_AclResource
     */
    private $resourceModel;

    /**
     * ResourcesCollector constructor.
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
     * @param \Spotman\Acl\Acl $acl
     */
    public function collectResources(Acl $acl)
    {
        $resources = $this->resourceModel->getAllResources();

        foreach ($resources as $resource) {
            $acl->addResource($resource->getCodename(), $resource->getParentResourceCodename());
        }
    }
}
