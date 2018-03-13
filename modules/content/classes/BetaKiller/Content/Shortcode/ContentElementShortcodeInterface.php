<?php
namespace BetaKiller\Content\Shortcode;

interface ContentElementShortcodeInterface extends ItemBasedShortcodeInterface
{
    public const ATTR_LAYOUT = 'layout';

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
