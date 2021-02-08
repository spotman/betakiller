<?php
declare(strict_types=1);

namespace BetaKiller\Acl;

use BetaKiller\Acl\Spec\EntityAclSpecInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use BetaKiller\Model\EntityWithAclSpecInterface;

final class EntityAclSpecFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * EntityAclSpecFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder->createFactory()
            ->setClassNamespaces('Acl', 'Spec')
            ->setClassSuffix('AclSpec')
            ->setExpectedInterface(EntityAclSpecInterface::class)
            ->cacheInstances();
    }

    public function createFor(EntityWithAclSpecInterface $entity): EntityAclSpecInterface
    {
        $name = $entity::getModelName();

        return $this->factory->create($name);
    }
}
