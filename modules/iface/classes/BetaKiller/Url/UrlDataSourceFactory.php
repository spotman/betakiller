<?php
namespace BetaKiller\Url;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use BetaKiller\Factory\RepositoryFactory;

/**
 * Class UrlDataSourceFactory
 *
 * @package BetaKiller\Url
 */
class UrlDataSourceFactory
{
    private NamespaceBasedFactoryInterface $factory;

    /**
     * UrlDataSourceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder->createFactory();

        // Using the same definitions as the RepositoryFactory does
        RepositoryFactory::injectDefinitions($this->factory);

        // Override repository definition
        $this->factory->setExpectedInterface(UrlDataSourceInterface::class);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlDataSourceInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename): UrlDataSourceInterface
    {
        return $this->factory->create($codename);
    }
}
