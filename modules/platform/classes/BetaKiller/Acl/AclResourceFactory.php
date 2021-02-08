<?php
namespace BetaKiller\Acl;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourceInterface;

class AclResourceFactory implements AclResourceFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * AclResourceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->cacheInstances()
            ->setClassNamespaces('Acl', 'Resource')
            ->setClassSuffix('Resource')
            ->setExpectedInterface(ResourceInterface::class);
    }

    /**
     * @param string $identity
     *
     * @return ResourceInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createResource(string $identity): ResourceInterface
    {
        return $this->factory->create(ucfirst($identity));
    }
}
