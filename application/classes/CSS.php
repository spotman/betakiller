<?php

class CSS implements CommonStaticInterface
{
    /**
     * @var \StaticCss
     */
    private $staticCss;

    /**
     * CSS constructor.
     *
     * @param \StaticCss $css
     */
    public function __construct(\StaticCss $css)
    {
        $this->staticCss = $css;
    }

    /**
     * Add public stylesheet via HTTP path
     *
     * @param string $url
     *
     * @return string HTML code
     */
    public function addPublic(string $url): string
    {
        // Add slash if not exists
        if (mb_strpos($url, 'http') !== 0 && mb_strpos($url, '/') !== 0) {
            $url = '/'.$url;
        }

        return $this->staticCss->addCss($url);
    }

    /**
     * Add local stylesheet from static-files directory
     *
     * @param string $filename
     *
     * @return string HTML code
     */
    public function addStatic(string $filename): string
    {
        return $this->staticCss->addCssStatic($filename);
    }

    /**
     * Add inline CSS
     *
     * @param string $string
     *
     * @deprecated
     */
    public function addInline(string $string): void
    {
        $this->staticCss->addCssInline($string);
    }

    /**
     * @return null|string
     */
    public function getFiles(): ?string
    {
        return $this->staticCss->getCss();
    }

    /**
     * @return null|string
     */
    public function getInline(): ?string
    {
        return $this->staticCss->getCssInline();
    }

    /**
     * @return string
     */
    public function getAll(): string
    {
        return $this->getFiles().$this->getInline();
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->staticCss->clear();
    }
}
