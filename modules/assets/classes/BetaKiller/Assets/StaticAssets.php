<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Helper\AppEnvInterface;
use HTML;
use Kohana;

class StaticAssets
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var array
     */
    private $config;

    /**
     * CSS files
     *
     * @var array
     */
    private $cssData = [];

    /**
     * Javascript files
     *
     * @var array
     */
    private $jsData = [];

    /**
     * JS constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \BetaKiller\Assets\AssetsConfig    $config
     */
    public function __construct(AppEnvInterface $appEnv, AssetsConfig $config)
    {
        $this->appEnv = $appEnv;
        $this->config = $config;
    }

    public function addJs(string $location): void
    {
        if ($this->isAbsoluteUrl($location)) {
            $this->addPublicJs($location);
        } else {
            $this->addStaticJs($location);
        }
    }

    public function addCss(string $location): void
    {
        if ($this->isAbsoluteUrl($location)) {
            $this->addPublicCss($location);
        } else {
            $this->addStaticCss($location);
        }
    }

    /**
     * @return string
     */
    public function renderJs(): string
    {
        $jsCode = '';

        foreach ($this->jsData as $condition => $jsArray) {
            foreach ($jsArray as $js) {
                $jsCode .= $this->getScriptTag($js, $condition)."\n";
            }
        }

        return $jsCode;
    }

    /**
     * @return string
     */
    public function renderCss(): string
    {
        $cssCode = '';

        // Not need to build one css file
        foreach ($this->cssData as $condition => $cssArray) {
            foreach ($cssArray as $css) {
                $cssCode .= $this->getCssLink($css, $condition);
            }
        }

        return $cssCode;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getFullUrl(string $path): string
    {
        return $this->getBaseUrl().$path;
    }

    /**
     * Поиск по проекту статичного файла
     * (полный путь к файлу)
     *
     * @param string $relativePath
     *
     * @return string
     */
    public function findFile(string $relativePath): ?string
    {
        $info = pathinfo($relativePath);
        $dir  = ($info['dirname'] !== '.') ? $info['dirname'].'/' : '';

        $file = Kohana::find_file('static-files', $dir.$info['filename'], $info['extension']);

        return $file ? (string)$file : null;
    }

    public function getDeployPath(string $relativePath): string
    {
        return $this->getFullPath().$relativePath;
    }

    private function getBasePath(): string
    {
        return $this->config->getUrlPath().DIRECTORY_SEPARATOR.'static/';
    }

    private function getFullPath(): string
    {
        return $this->appEnv->getDocRootPath().$this->getBasePath();
    }

    private function isAbsoluteUrl(string $location): bool
    {
        return \mb_strpos($location, 'http') === 0 || \mb_strpos($location, '//') === 0;
    }

    /**
     * Include public js file in a page
     *
     * @param string      $location
     * @param string|null $condition
     *
     * @return void
     */
    private function addPublicJs(string $location, string $condition = null): void
    {
        $this->jsData[$condition][] = $location;
    }

    /**
     * Include local js file from static-files directory
     *
     * @param string      $filename
     *
     * @param string|null $condition
     *
     * @return void
     */
    private function addStaticJs(string $filename, string $condition = null): void
    {
        $jsFile = $this->getFullUrl($filename);

        $this->addPublicJs($jsFile, $condition);
    }

    /**
     * Add public stylesheet via HTTP path
     *
     * @param string      $location
     * @param string|null $condition
     *
     * @return void
     */
    private function addPublicCss(string $location, string $condition = null): void
    {
        $this->cssData[$condition][] = $location;
    }

    /**
     * Add local stylesheet from static-files directory
     *
     * @param string      $filename
     * @param string|null $condition
     *
     * @return void
     */
    private function addStaticCss(string $filename, string $condition = null): void
    {
        $cssFile = $this->getFullUrl($filename);

        $this->addPublicCss($cssFile, $condition);
    }

    private function getBaseUrl(): string
    {
        // TODO
        return '/assets/static/';
    }

    /**
     * Gets html code of the css loading
     *
     * @param  string      $location
     * @param  string|null $condition
     *
     * @return string
     */
    private function getCssLink(string $location, string $condition = null): string
    {
        $location = $this->prepareLocation($location);

        return ''
            .($condition ? '<!--[if '.$condition.']>' : '')
            .HTML::style($location, ['media' => 'all'])
            .($condition ? '<![endif]-->' : '');
    }

    /**
     * Gets html code of the script loading
     *
     * @param  string      $location
     * @param  string|null $condition
     *
     * @return string
     */
    private function getScriptTag(string $location, string $condition = null): string
    {
        $location = $this->prepareLocation($location);

        return ''
            .($condition ? '<!--[if '.$condition.']>' : '')
            .HTML::script($location)
            .($condition ? '<![endif]-->' : '')."\n";
    }

    private function prepareLocation(string $location): string
    {
//        if (!$this->isAbsoluteUrl($location)) {
//            TODO Deal with adding domain name here
//            $location = trim($location, '/');
//            $location = ($this->_config->host === '/') ? $location : $this->_config->host.$location;
//        }

        return $location;
    }
}
