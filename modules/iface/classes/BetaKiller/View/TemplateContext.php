<?php

namespace BetaKiller\View;

use BetaKiller\Assets\StaticAssets;
use BetaKiller\Model\LanguageInterface;
use Meta;
use Psr\Http\Message\ServerRequestInterface;

class TemplateContext
{
    private const WRAPPER_HTML5 = 'html5';

    public const  KEY_CONTEXT = '__context__';

    public const  KEY_REQUEST = '__request__';
    public const  KEY_ASSETS  = '__assets__';
    public const  KEY_META    = '__meta__';
    public const  KEY_LANG    = '__lang__';

    /**
     * @var string|null
     */
    private ?string $wrapper = null;

    /**
     * @var string|null
     */
    private ?string $layout = null;

    /**
     * HtmlRenderHelper constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Meta                                    $meta
     * @param \BetaKiller\Assets\StaticAssets          $assets
     * @param \BetaKiller\Model\LanguageInterface      $lang
     */
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly Meta $meta,
        private readonly StaticAssets $assets,
        private readonly LanguageInterface $lang
    ) {
        $this->meta->set('content-language', $lang->getIsoCode());

        $this->setContentType('text/html');
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function hasLayout(): bool
    {
        return !empty($this->layout);
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     *
     * @return \BetaKiller\View\TemplateContext
     */
    public function setLayout(string $layout): TemplateContext
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasWrapper(): bool
    {
        return !empty($this->wrapper);
    }

    /**
     * @return string
     */
    public function getWrapper(): string
    {
        return $this->wrapper;
    }

    public function wrapInHtml5(): TemplateContext
    {
        return $this->setWrapper(self::WRAPPER_HTML5);
    }

    /**
     * Set HTML <title> tag text
     *
     * @param string $title
     *
     * @return \BetaKiller\View\TemplateContext
     */
    public function setTitle(string $title): TemplateContext
    {
        $this->meta->setTitle($title, Meta::TITLE_APPEND);

        return $this;
    }

    /**
     * Set <meta> description tag value
     *
     * @param string $value
     *
     * @return \BetaKiller\View\TemplateContext
     */
    public function setMetaDescription(string $value): TemplateContext
    {
        $this->meta->setDescription($value);

        return $this;
    }

    public function setMetaCanonical(string $url): TemplateContext
    {
        $this->meta->setCanonical($url);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\View\TemplateContext
     */
    public function setContentType(string $value): self
    {
        $this->meta->setContentType($value);

        return $this;
    }

    public function getTemplateData(): array
    {
        return [
            // Bind context for configuring wrapper from template
            self::KEY_CONTEXT => $this,

            // Send current request to widgets
            self::KEY_REQUEST => $this->request,

            // Assets instance for adding js/css files
            self::KEY_ASSETS  => $this->assets,

            // Meta instance for adding <meta> tags
            self::KEY_META    => $this->meta,

            // Language to perform i18n
            self::KEY_LANG    => $this->lang,
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
            $this->assets->renderCss(),
        ]);
    }

    private function renderFooter(): string
    {
        return $this->assets->renderJs();
    }

    /**
     * @param string $wrapper
     *
     * @return \BetaKiller\View\TemplateContext
     */
    private function setWrapper(string $wrapper): TemplateContext
    {
        $this->wrapper = $wrapper;

        return $this;
    }
}
