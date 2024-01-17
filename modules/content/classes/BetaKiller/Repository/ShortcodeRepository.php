<?php
namespace BetaKiller\Repository;

use BetaKiller\Content\Shortcode\ShortcodeEntity;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
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
     * @return \BetaKiller\Content\Shortcode\ShortcodeEntityInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByTagName(string $tagName): ShortcodeEntityInterface
    {
        return $this->getOneByOptionValue(ShortcodeEntityInterface::OPTION_TAG_NAME, $tagName);
    }

    /**
     * @return \BetaKiller\Content\Shortcode\ShortcodeEntityInterface[]
     */
    public function getContentElementShortcodes(): array
    {
        return $this->findByType(ShortcodeEntityInterface::TYPE_CONTENT_ELEMENT);
    }

    /**
     * @return \BetaKiller\Content\Shortcode\ShortcodeEntityInterface[]
     */
    public function getStaticShortcodes(): array
    {
        return $this->findByType(ShortcodeEntityInterface::TYPE_STATIC);
    }

    /**
     * @return \BetaKiller\Content\Shortcode\ShortcodeEntityInterface[]
     */
    public function getDynamicShortcodes(): array
    {
        return $this->findByType(ShortcodeEntityInterface::TYPE_DYNAMIC);
    }

    /**
     * @return string[]
     */
    public function getEditableTagsNames(): array
    {
        $output = [];

        foreach ($this->getAll() as $entity) {
            if ($entity->isEditable()) {
                $output[] = $entity->getTagName();
            }
        }

        return $output;
    }

    /**
     * @param string $type
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeEntityInterface[]
     */
    protected function findByType(string $type): array
    {
        return $this->findAllByOptionValue(ShortcodeEntityInterface::OPTION_TYPE, $type);
    }

    protected function getItemsListConfigGroup(): string
    {
        return 'content';
    }

    protected function getItemsListConfigPath(): array
    {
        return ['shortcodes'];
    }

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return ShortcodeEntityInterface::URL_KEY;
    }

    /**
     * @param string     $codename
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

    protected function prepareCodename(string $codename): string
    {
        // Use class-based codenames
        return \ucfirst($codename);
    }
}
