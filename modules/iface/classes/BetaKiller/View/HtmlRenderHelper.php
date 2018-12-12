<?php
namespace BetaKiller\View;

use BetaKiller\Assets\StaticAssets;
use BetaKiller\Model\LanguageInterface;
use HTML;

class HtmlRenderHelper
{
    public const WRAPPER_HTML5 = 'html5';

    /**
     * @var \Meta
     */
    private $meta;

    /**
     * @var \BetaKiller\Assets\StaticAssets
     */
    private $assets;

    /**
     * @var LanguageInterface
     */
    private $lang;

    /**
     * @var string[][]
     */
    private $links = [];

    /**
     * @var string
     */
    private $wrapperCodename = self::WRAPPER_HTML5;

    /**
     * @var string
     */
    private $layoutCodename;

    /**
     * HtmlRenderHelper constructor.
     *
     * @param \Meta                           $meta
     * @param \BetaKiller\Assets\StaticAssets $assets
     */
    public function __construct(\Meta $meta, StaticAssets $assets)
    {
        $this->meta   = $meta;
        $this->assets = $assets;
    }

    /**
     * @return string
     */
    public function getLayoutCodename(): string
    {
        return $this->layoutCodename;
    }

    /**
     * @param string $layoutCodename
     *
     * @return \BetaKiller\View\HtmlRenderHelper
     */
    public function setLayoutCodename(string $layoutCodename): HtmlRenderHelper
    {
        $this->layoutCodename = $layoutCodename;

        return $this;
    }

    /**
     * @return string
     */
    public function getWrapperCodename(): string
    {
        return $this->wrapperCodename;
    }

    /**
     * @param string $wrapperCodename
     *
     * @return \BetaKiller\View\HtmlRenderHelper
     */
    public function setWrapperCodename(string $wrapperCodename): HtmlRenderHelper
    {
        $this->wrapperCodename = $wrapperCodename;

        return $this;
    }

    /**
     * Set HTML <title> tag text
     *
     * @param string $title
     *
     * @return \BetaKiller\View\HtmlRenderHelper
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
     * @return \BetaKiller\View\HtmlRenderHelper
     */
    public function setMetaDescription(string $value): self
    {
        $this->meta->description($value);

        return $this;
    }

    /**
     * Set <link rel="canonical"> value
     *
     * @param string    $href
     * @param bool|null $overwrite
     *
     * @return \BetaKiller\View\HtmlRenderHelper
     */
    public function setCanonical(string $href, ?bool $overwrite = null): self
    {
        // Prevent overwrite of `canonical` value by error pages and nested IFaces
        if (!$overwrite && $this->hasLink('canonical')) {
            return $this;
        }

        return $this->addLink('canonical', $href);
    }

    /**
     * @param null|string $value
     *
     * @return \BetaKiller\View\HtmlRenderHelper
     */
    public function setContentType(string $value = null): self
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

    public function hasLink(string $rel): bool
    {
        foreach ($this->links as $attributes) {
            if ($attributes['rel'] === $rel) {
                return true;
            }
        }

        return false;
    }

    public function setLang(LanguageInterface $lang): self
    {
        $this->lang = $lang;

        $this->meta->set('content-language', $lang->getIsoCode());

        return $this;
    }

    public function getLayoutHelperObjects(): array
    {
        return [
            IFaceView::ASSETS_KEY => $this->assets,
            IFaceView::META_KEY   => $this->meta,
        ];
    }

    public function getWrapperData(): array
    {
        return [
            'lang'   => $this->lang->getIsoCode(),
            'header' => $this->renderHeader(),
            'footer' => $this->renderFooter(),
        ];
    }

    private function renderHeader(): string
    {
        return implode("\r\n", [
            $this->meta->render(),
            $this->renderLinks(),
            $this->assets->renderCss(),
        ]);
    }

    private function renderFooter(): string
    {
        return $this->assets->renderJs();
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
