<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Model\EntityModelInterface;

interface ContentElementShortcodeInterface extends ShortcodeInterface
{
    public const ATTR_LAYOUT = 'layout';

    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Content\Shortcode\Editor\EditorListingItem[]
     */
    public function getEditorListingItems(?EntityModelInterface $relatedEntity, ?int $itemID): array;

    /**
     * @return int|null
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getID(): ?int;

    /**
     * @param int $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setID(int $value): void;

    /**
     * @param bool|null $useDefaultIfEmpty
     *
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getLayout(?bool $useDefaultIfEmpty = null): ?string;

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useDefaultLayout(): void;

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getDefaultLayout(): string;
}
