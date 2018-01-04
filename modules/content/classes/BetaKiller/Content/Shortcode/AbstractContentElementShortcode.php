<?php
namespace BetaKiller\Content\Shortcode;

abstract class AbstractContentElementShortcode extends AbstractEditableShortcode
{
    public const ATTR_LAYOUT = 'layout';

    public function getID(): ?int
    {
        return $this->getAttribute('id');
    }

    public function setID(int $value): void
    {
        $this->setAttribute('id', $value);
    }

    public function getLayout(): ?string
    {
        return $this->getAttribute(self::ATTR_LAYOUT);
    }

    protected function setLayout(string $value): void
    {
        $this->setAttribute(self::ATTR_LAYOUT, $value);
    }
}
