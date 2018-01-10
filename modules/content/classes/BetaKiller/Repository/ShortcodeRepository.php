<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\Shortcode\ShortcodeEntity;
use BetaKiller\Model\ConfigBasedDispatchableEntityInterface;

/**
 * Class ParserRepository
 *
 * @package BetaKiller\Content
 *
 * @method ShortcodeEntity findByCodename(string $name)
 * @method ShortcodeEntity[] getAll()
 */
class ShortcodeRepository extends AbstractConfigBasedDispatchableRepository
{
    /**
     * @param string $tagName
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeEntity
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByTagName(string $tagName): ShortcodeEntity
    {
        return $this->findByOptionValue(ShortcodeEntity::OPTION_TAG_NAME, $tagName);
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
     * @param string $codename
     *
     * @param array|null $options
     *
     * @return ConfigBasedDispatchableEntityInterface|mixed
     */
    protected function createItemFromCodename(
        string $codename,
        ?array $options = null
    ): ConfigBasedDispatchableEntityInterface {
        return new ShortcodeEntity($codename, $options);
    }
}
