<?php
namespace BetaKiller\Acl;

use Spotman\Acl\ResourcesCollector\AbstractResourcesCollector;
use Model_AclResource;

class ResourcesCollector extends AbstractResourcesCollector
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
     */
    public function collectResources()
    {
        $resources = $this->resourceModel->getAllResources();

        foreach ($resources as $resource) {
            $this->addResource($resource->getResourceId(), $resource->getParentResourceId());
        }
    }
}
