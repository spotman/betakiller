<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use Spotman\Defence\Parameter\ParameterProviderFactoryInterface;
use Spotman\Defence\Parameter\ParameterProviderInterface;
use Spotman\Defence\ParameterArgumentDefinitionInterface;
use Spotman\Defence\ArgumentDefinitionInterface;

class DefenceParameterProviderFactory implements ParameterProviderFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private Factory\NamespaceBasedFactory $factory;

    /**
     * DefenceParameterProviderFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $builder)
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
