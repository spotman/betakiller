<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use Spotman\Defence\ArgumentDefinitionInterface;
use Spotman\Defence\Parameter\ParameterProviderFactoryInterface;
use Spotman\Defence\Parameter\ParameterProviderInterface;
use Spotman\Defence\ParameterArgumentDefinitionInterface;

class DefenceParameterProviderFactory implements ParameterProviderFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * DefenceParameterProviderFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder->createFactory()
            ->cacheInstances()
            ->setClassNamespaces('Defence', 'ParameterProvider')
            ->setClassSuffix('ParameterProvider')
            ->setExpectedInterface(ParameterProviderInterface::class);
    }

    public function createFor(ArgumentDefinitionInterface $argDef): ParameterProviderInterface
    {
        if (!$argDef instanceof ParameterArgumentDefinitionInterface) {
            throw new \LogicException(
                sprintf('Defence parameter "%s" must implement %s', $argDef->getName(),
                    ParameterArgumentDefinitionInterface::class)
            );
        }

        return $this->factory->create($argDef->getCodename());
    }
}
