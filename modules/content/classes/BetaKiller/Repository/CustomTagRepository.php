<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\CustomTag\CustomTagUrlParameter;
use BetaKiller\IFace\Url\ConfigBasedUrlParameterInterface;

/**
 * Class ParserRepository
 *
 * @package BetaKiller\Content
 *
 * @method CustomTagUrlParameter findByName(string $name)
 * @method CustomTagUrlParameter[] getAll()
 */
class CustomTagRepository extends AbstractConfigBasedUrlParameterRepository
{
    protected function getItemsListConfigKey(): array
    {
        return ['content', 'custom_tags'];
    }

    /**
     * @param string $codename
     *
     * @return ConfigBasedUrlParameterInterface|mixed
     */
    protected function createItemFromCodename(string $codename): ConfigBasedUrlParameterInterface
    {
        return new CustomTagUrlParameter($codename);
    }
}
