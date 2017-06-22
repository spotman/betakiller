<?php
namespace BetaKiller\Acl;

use BetaKiller\Factory\NamespaceBasedFactory;
use Spotman\Acl\Exception;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourceInterface;

class AclResourceFactory implements AclResourceFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory;
     */
    private $factory;

    /**
     * AclResourceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory) {
        $this->factory = $factory
            ->cacheInstances()
            ->setClassPrefixes('Acl', 'Resource')
            ->setClassSuffix('Resource')
            ->setExpectedInterface(ResourceInterface::class);
    }

    /**
     * @param string $identity
     *
     * @return ResourceInterface
     * @throws Exception
     */
    public function createResource($identity) {
        return $this->factory->create(ucfirst($identity));
    }
}
