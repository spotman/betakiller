<?php
namespace BetaKiller\Repository;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Content\CustomTag\CustomTagFactory;
use BetaKiller\Content\CustomTag\CustomTagInterface;

/**
 * Class ParserRepository
 *
 * @package BetaKiller\Content
 *
 * @method CustomTagInterface findByName(string $name)
 * @method CustomTagInterface[] getAll()
 */
class CustomTagRepository extends AbstractConfigBasedUrlParameterRepository
{
    /**
     * @var \BetaKiller\Content\CustomTag\CustomTagFactory
     */
    private $factory;

    /**
     * ParserRepository constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     * @param CustomTagFactory                           $factory
     */
    public function __construct(ConfigProviderInterface $configProvider, CustomTagFactory $factory)
    {
        $this->configProvider = $configProvider;
        $this->factory        = $factory;

        parent::__construct($configProvider);
    }

    protected function getItemsListConfigKey(): array
    {
        return ['content', 'custom_tags'];
    }

    /**
     * @param string $codename
     *
     * @return mixed
     */
    protected function createItemFromCodename(string $codename): CustomTagInterface
    {
        return $this->factory->create($codename);
    }
}
