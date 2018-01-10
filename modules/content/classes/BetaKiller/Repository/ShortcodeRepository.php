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
    /**
     * @param string $tagName
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeUrlParameter
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByTagName(string $tagName): ShortcodeUrlParameter
    {
        return $this->findByOptionValue(ShortcodeUrlParameter::OPTION_TAG_NAME, $tagName);
    }

    protected function getItemsListConfigKey(): array
    {
        return ['content', 'shortcodes'];
    }

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return 'tagName';
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
