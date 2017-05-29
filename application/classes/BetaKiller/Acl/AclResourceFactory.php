<?php
namespace BetaKiller\Acl;

use BetaKiller\Factory\NamespaceBasedFactory;
use Spotman\Acl\AccessResolver\AclAccessResolverInterface;
use Spotman\Acl\Exception;
use Spotman\Acl\Resource\ResolvingResourceInterface;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourceInterface;

class AclResourceFactory implements AclResourceFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory;
     */
    private $factory;

    /**
     * @var \Spotman\Acl\AccessResolver\AclAccessResolverInterface
     */
    private $resolver;

    /**
     * AclResourceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory              $factory
     * @param \Spotman\Acl\AccessResolver\AclAccessResolverInterface $resolver
     */
    public function __construct(NamespaceBasedFactory $factory, AclAccessResolverInterface $resolver)
    {
        $this->factory = $factory
            ->cacheInstances()
            ->setClassPrefixes('Acl', 'Resource')
            ->setClassSuffix('Resource')
            ->setExpectedInterface(ResourceInterface::class);

        $this->resolver = $resolver;
    }

    /**
     * @param string $identity
     *
     * @return ResourceInterface
     * @throws Exception
     */
    public function createResource($identity)
    {
        $resource = $this->factory->create(ucfirst($identity));

        if ($resource instanceof ResolvingResourceInterface) {
            $resource->useResolver($this->resolver);
        }

        return $resource;
    }
}
