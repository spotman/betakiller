<?php
declare(strict_types=1);

namespace BetaKiller\Acl;

use BetaKiller\Acl\Spec\EntityAclSpecInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Model\AbstractEntityInterface;

final class EntityAclSpecFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * EntityAclSpecFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $builder
     */
    public function __construct(NamespaceBasedFactoryBuilder $builder)
    {
        $this->factory = $builder->createFactory()
            ->setClassNamespaces('Acl', 'Spec')
            ->setClassSuffix('AclSpec')
            ->setExpectedInterface(EntityAclSpecInterface::class)
            ->cacheInstances();
    }

    public function createFor(AbstractEntityInterface $entity): EntityAclSpecInterface
    {
        $name = $entity::getModelName();

        return $this->factory->create($name);
    }
}
