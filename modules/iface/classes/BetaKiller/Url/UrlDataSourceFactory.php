<?php
namespace BetaKiller\Url;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Factory\RepositoryFactory;

/**
 * Class UrlDataSourceFactory
 *
 * @package BetaKiller\Url
 */
class UrlDataSourceFactory
{
    private $factory;

    /**
     * UrlDataSourceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     * @param \BetaKiller\Factory\RepositoryFactory            $repositoryFactory
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder, RepositoryFactory $repositoryFactory)
    {
        $this->factory = $factoryBuilder->createFactory();

        // Using the same definitions as the RepositoryFactory does
        $repositoryFactory->injectDefinitions($this->factory);

        // Override repository definition
        $this->factory->setExpectedInterface(UrlDataSourceInterface::class);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlDataSourceInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create($codename): UrlDataSourceInterface
    {
        return $this->factory->create($codename);
    }
}
