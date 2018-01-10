<?php
namespace BetaKiller\Url;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\Factory\RepositoryFactory;

/**
 * Class UrlDataSourceFactory
 *
 * @package BetaKiller\Url
 */
class UrlDataSourceFactory
{
    private $factory;

    public function __construct(NamespaceBasedFactory $factory, RepositoryFactory $repositoryFactory)
    {
        // Using the same definitions as the RepositoryFactory does
        $repositoryFactory->injectDefinitions($factory);

        $this->factory = $factory->setExpectedInterface(UrlDataSourceInterface::class);
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
