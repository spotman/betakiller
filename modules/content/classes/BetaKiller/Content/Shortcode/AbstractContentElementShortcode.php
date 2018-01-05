<?php
namespace BetaKiller\Content\Shortcode;

abstract class AbstractContentElementShortcode extends AbstractEditableShortcode
{
    public const ATTR_LAYOUT = 'layout';

    /**
     * @return int|null
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getID(): ?int
    {
        return $this->getAttribute('id');
    }

    /**
     * @param int $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setID(int $value): void
    {
        $this->setAttribute('id', $value);
    }

    /**
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getLayout(): ?string
    {
        return $this->getAttribute(self::ATTR_LAYOUT);
    }

    /**
     * @param string $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    protected function setLayout(string $value): void
    {
        $this->setAttribute(self::ATTR_LAYOUT, $value);
    }
}
