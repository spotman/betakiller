<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

/**
 * Class RawUrlParameterFactory
 *
 * @package BetaKiller\Url
 */
class RawUrlParameterFactory
{
    private NamespaceBasedFactoryInterface $factory;

    /**
     * RawUrlParameterFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces(...RawUrlParameterInterface::CLASS_NS)
            ->setClassSuffix(RawUrlParameterInterface::CLASS_SUFFIX)
            ->setExpectedInterface(RawUrlParameterInterface::class);
    }

    /**
     * @param string $codename
     * @param string $uriValue
     *
     * @return \BetaKiller\Url\Parameter\RawUrlParameterInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename, string $uriValue): RawUrlParameterInterface
    {
        /** @var \BetaKiller\Url\Parameter\RawUrlParameterInterface $instance */
        $instance = $this->factory->create($codename, [
            'value' => $uriValue,
        ]);

        return $instance;
    }
}
