<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

/**
 * Class RawUrlParameterFactory
 *
 * @package BetaKiller\Url
 */
class RawUrlParameterFactory
{
    private $factory;

    /**
     * RawUrlParameterFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
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
