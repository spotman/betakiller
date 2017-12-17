<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\Shortcode\ShortcodeUrlParameter;
use BetaKiller\IFace\Url\ConfigBasedUrlParameterInterface;

/**
 * Class ParserRepository
 *
 * @package BetaKiller\Content
 *
 * @method ShortcodeUrlParameter findByCodename(string $name)
 * @method ShortcodeUrlParameter[] getAll()
 */
class ShortcodeRepository extends AbstractConfigBasedUrlParameterRepository
{
    protected function getItemsListConfigKey(): array
    {
        return ['content', 'shortcodes'];
    }

    /**
     * @param string     $codename
     *
     * @param array|null $options
     *
     * @return ConfigBasedUrlParameterInterface|mixed
     */
    protected function createItemFromCodename(string $codename, ?array $options = null): ConfigBasedUrlParameterInterface
    {
        return new ShortcodeUrlParameter($codename, $options);
    }
}
