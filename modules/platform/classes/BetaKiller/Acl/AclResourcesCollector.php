<?php
namespace BetaKiller\Acl;

use BetaKiller\Repository\AclResourceRepository;
use Spotman\Acl\AclInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;

class AclResourcesCollector implements AclResourcesCollectorInterface
{
    /**
     * @var \BetaKiller\Repository\AclResourceRepository
     */
    private $resourceRepository;

    /**
     * AclResourcesCollector constructor.
     *
     * @param \BetaKiller\Repository\AclResourceRepository $repo
     */
    public function __construct(AclResourceRepository $repo)
    {
        $this->resourceRepository = $repo;
    }

    /**
     * Collect resources from external source and add them to acl via protected methods addResource
     *
     * @param \Spotman\Acl\AclInterface $acl
     */
    public function collectResources(AclInterface $acl): void
    {
        foreach ($this->resourceRepository->getAll() as $resource) {
            $acl->addResource($resource->getCodename(), $resource->getParentResourceCodename());
        }
    }
}
