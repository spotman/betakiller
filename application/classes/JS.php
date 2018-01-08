<?php

class JS
{
    use \BetaKiller\Utils\Instance\SingletonTrait;

    /**
     * @var \StaticJs
     */
    private $staticJs;

    /**
     * JS constructor.
     *
     * @param \StaticJs $staticJs
     */
    public function __construct(\StaticJs $staticJs)
    {
        $this->staticJs = $staticJs;
    }

    /**
     * Include public js file in a page
     *
     * @param $url
     *
     * @return string HTML code
     */
    public function addPublic(string $url): string
    {
        // Добавляем слеш в начале, если его нет
        if (mb_strpos($url, 'http') !== 0 && mb_strpos($url, '/') !== 0) {
            $url = '/'.$url;
        }

        return $this->staticJs->addJs($url);
    }

    /**
     * Include local js file from static-files directory
     *
     * @param string $filename
     *
     * @return string HTML code
     */
    public function addStatic(string $filename): string
    {
        return $this->staticJs->addJsStatic($filename);
    }

    /**
     * Add inline script
     *
     * @param string $string
     * @deprecated
     */
    public function addInline(string $string): void
    {
        $this->staticJs->addJsInline($string);
    }

    /**
     * Add js code which would be run at page load
     *
     * @param $string
     * @deprecated
     */
    public function addOnload(string $string): void
    {
        $this->staticJs->addJsOnload($string);
    }

    /**
     * @return null|string
     */
    public function getFiles(): ?string
    {
        return $this->staticJs->getJs();
    }

    /**
     * @return null|string
     */
    public function getInline(): ?string
    {
        return $this->staticJs->getJsInline();
    }

    /**
     * @return string
     */
    public function getAll(): string
    {
        return $this->getFiles().$this->getInline();
    }
}
