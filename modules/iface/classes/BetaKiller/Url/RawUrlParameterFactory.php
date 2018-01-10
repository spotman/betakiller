<?php
namespace BetaKiller\Url;

use BetaKiller\Factory\NamespaceBasedFactory;

/**
 * Class RawUrlParameterFactory
 *
 * @package BetaKiller\Url
 */
class RawUrlParameterFactory
{
    private $factory;

    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setClassNamespaces(RawUrlParameterInterface::CLASS_NS)
            ->setClassSuffix(RawUrlParameterInterface::CLASS_SUFFIX)
            ->setExpectedInterface(RawUrlParameterInterface::class);
    }

    /**
     * @param string $codename
     * @param string $uriValue
     *
     * @return \BetaKiller\Url\RawUrlParameterInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename, string $uriValue): RawUrlParameterInterface
    {
        /** @var \BetaKiller\Url\RawUrlParameterInterface $instance */
        $instance = $this->factory->create($codename);

        // Inject uri value and parse it
        $instance->importUriValue($uriValue);

        return $instance;
    }
}
