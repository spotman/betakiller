<?php
namespace BetaKiller\Content\Shortcode;

interface ContentElementShortcodeInterface extends ItemBasedShortcodeInterface
{
    public const ATTR_LAYOUT = 'layout';

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
