<?php
namespace BetaKiller\View;

use HTML;

class LinkTagHelper
{
    /**
     * @var string[][]
     */
    private $links = [];

    /**
     * Set <link rel="canonical"> value
     *
     * @param string    $href
     * @param bool|null $overwrite
     *
     * @return \BetaKiller\View\LinkTagHelper
     */
    public function setCanonical(string $href, ?bool $overwrite = null): self
    {
        // Prevent overwrite of `canonical` value by error pages and nested IFaces
        if (!$overwrite && $this->hasLink('canonical')) {
            return $this;
        }

        return $this->addLink('canonical', $href);
    }

    public function addLink(string $rel, string $href, ?array $attributes = null): self
    {
        $attributes = $attributes ?? [];

        $attributes['rel']  = $rel;
        $attributes['href'] = $href;

        $this->links[] = $attributes;

        return $this;
    }

    public function hasLink(string $rel): bool
    {
        foreach ($this->links as $attributes) {
            if ($attributes['rel'] === $rel) {
                return true;
            }
        }

        return false;
    }

    public function renderLinks(): string
    {
        $output = [];

        foreach ($this->links as $item) {
            $output[] = '<link'.HTML::attributes($item).' />';
        }

        return implode("\r\n", $output);
    }
}
