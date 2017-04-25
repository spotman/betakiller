<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\Factory\OrmFactory;

/**
 * Class UrlDataSourceFactory
 *
 * @package BetaKiller\IFace\Url
 */
class UrlDataSourceFactory
{
    private $factory;

    public function __construct(NamespaceBasedFactory $factory, OrmFactory $ormFactory)
    {
        // Using the same definitions as the OrmFactory does
        $ormFactory->injectDefinitions($factory);

        $this->factory = $factory->setExpectedInterface(UrlDataSourceInterface::class);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\Url\UrlDataSourceInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create($codename)
    {
        return $this->factory->create($codename);
    }
}
