<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\DiscreteValuesAttribute;
use BetaKiller\Content\Shortcode\Attribute\IdAttribute;

abstract class AbstractContentElementShortcode extends AbstractShortcode
{
    public const ATTR_LAYOUT = 'layout';

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    protected function getDefinitions(): array
    {
        $definitions = $this->getContentElementShortcodeDefinitions();

        // Content elements must have ID
        $definitions[] = new IdAttribute();

        $layouts = $this->getAvailableLayouts();

        if ($layouts) {
            $definitions[] = new DiscreteValuesAttribute(self::ATTR_LAYOUT, $layouts, true);
        }

        return $definitions;
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    abstract protected function getContentElementShortcodeDefinitions(): array;

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
     * @param null|string $default
     *
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getLayout(?string $default = null): ?string
    {
        return $this->getAttribute(self::ATTR_LAYOUT) ?: $default;
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

    /**
     * @param string $value
     *
     * @return bool
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    protected function isLayout(string $value): bool
    {
        return $this->getLayout() === $value;
    }

    /**
     * @return string[]
     */
    abstract protected function getAvailableLayouts(): array;
}
