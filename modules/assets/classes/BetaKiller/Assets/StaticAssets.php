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
     * @var AssetsConfig
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

    public function addJs(string $location, ?string $condition = null): void
    {
        if (!$this->isAbsoluteUrl($location)) {
            $location = $this->getFullUrl($location);
        }

        // Skip duplicate calls from widgets
        if (isset($this->jsData[$location])) {
            return;
        }

        $this->jsData[$location] = [
            'location'  => $location,
            'condition' => $condition,
        ];
    }

    public function addCss(string $location, ?string $condition = null): void
    {
        if (!$this->isAbsoluteUrl($location)) {
            $location = $this->getFullUrl($location);
        }

        // Skip duplicate calls from widgets
        if (isset($this->cssData[$location])) {
            return;
        }

        $this->cssData[$location] = [
            'location'  => $location,
            'condition' => $condition,
        ];
    }

    /**
     * @return string
     */
    public function renderJs(): string
    {
        $jsCode = '';

        foreach ($this->jsData as $location => $jsData) {
            $jsCode .= $this->getScriptTag($location, $jsData['condition'])."\n";
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
        foreach ($this->cssData as $location => $cssData) {
            $cssCode .= $this->getCssLink($location, $cssData['condition'])."\n";
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
        return $this->config->getUrlPath().'/static/';
    }

    private function getFullPath(): string
    {
        return $this->appEnv->getDocRootPath().$this->getBasePath();
    }

    private function isAbsoluteUrl(string $location): bool
    {
        return \mb_strpos($location, 'http') === 0
            || \mb_strpos($location, '//') === 0
            || \mb_strpos($location, '/') === 0;
    }

    private function getBaseUrl(): string
    {
        // TODO Domain if needed
        return $this->getBasePath();
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
        $code = HTML::style($location, ['media' => 'all']);

        if ($condition) {
            $code = $this->wrapInCondition($code, $condition);
        }

        return $code;
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
        $code = HTML::script($location);

        if ($condition) {
            $code = $this->wrapInCondition($code, $condition);
        }

        return $code;
    }

    private function wrapInCondition(string $code, string $condition): string
    {
        return '<!--[if '.$condition.']>'.$code.'<![endif]-->';
    }
}
