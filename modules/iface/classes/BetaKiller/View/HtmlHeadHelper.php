<?php
namespace BetaKiller\View;

use HTML;

class HtmlHeadHelper
{
    /**
     * @var \Meta
     */
    private $meta;

    private $links = [];

    /**
     * HtmlHeadHelper constructor.
     *
     * @param \Meta $meta
     */
    public function __construct(\Meta $meta)
    {
        $this->meta = $meta;
    }

    /**
     * Set HTML <title> tag text
     *
     * @param string $title
     *
     * @return \BetaKiller\View\HtmlHeadHelper
     */
    public function setTitle(string $title): self
    {
        $this->meta->title($title, \Meta::TITLE_APPEND);

        return $this;
    }

    /**
     * Set <meta> description tag value
     *
     * @param string $value
     *
     * @return \BetaKiller\View\HtmlHeadHelper
     */
    public function setMetaDescription(string $value): self
    {
        $this->meta->description($value);

        return $this;
    }

    /**
     * Set <link rel="canonical"> value
     *
     * @param string $href
     *
     * @return \BetaKiller\View\HtmlHeadHelper
     */
    public function setCanonical(string $href): self
    {
        return $this->addLink('canonical', $href);
    }

    /**
     * @param null|string $value
     *
     * @return \BetaKiller\View\HtmlHeadHelper
     */
    public function setContentType(?string $value = null): self
    {
        $this->meta->content_type($value ?: 'text/html');

        return $this;
    }

    public function addLink(string $rel, string $href, array $attributes = null): self
    {
        $attributes = $attributes ?? [];

        $attributes['rel']  = $rel;
        $attributes['href'] = $href;

        $this->links[] = $attributes;

        return $this;
    }

    public function renderAll(): string
    {
        return $this->meta->render()."\r\n".$this->renderLinks();
    }

    private function renderLinks(): string
    {
        $output = [];

        foreach ($this->links as $item) {
            $output[] = '<link'.HTML::attributes($item).' />';
        }

        return implode("\r\n", $output);
    }
}
