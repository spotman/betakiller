<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\DiscreteValuesAttribute;
use BetaKiller\Content\Shortcode\Attribute\IdAttribute;

abstract class AbstractContentElementShortcode extends AbstractShortcode implements ContentElementShortcodeInterface
{
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
            $layoutAttribute = new DiscreteValuesAttribute(self::ATTR_LAYOUT, $layouts);
            $definitions[] = $layoutAttribute->optional($layouts[0]);
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
     * @param bool|null $useDefaultIfEmpty
     *
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getLayout(?bool $useDefaultIfEmpty = null): ?string
    {
        $useDefaultIfEmpty = $useDefaultIfEmpty ?? true;

        $layout = $this->getAttribute(self::ATTR_LAYOUT);

        if (!$layout && $useDefaultIfEmpty) {
            $layout = $this->getDefaultLayout();
        }

        return $layout;
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useDefaultLayout(): void
    {
        $this->setLayout($this->getDefaultLayout());
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getDefaultLayout(): string
    {
        $layouts = $this->getAvailableLayouts();

        if (!$layouts) {
            throw new ShortcodeException('Can not get default layout coz no layouts defined in shortcode :name', [
                ':name' => $this->getCodename(),
            ]);
        }

        return $layouts[0];
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
        return $this->getLayout(false) === $value;
    }

    /**
     * @return string[]
     */
    abstract protected function getAvailableLayouts(): array;
}
