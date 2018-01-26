<?php
namespace BetaKiller\Acl;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
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
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
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
