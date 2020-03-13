<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Helper\AppEnvInterface;
use HTML;
use Kohana;
use Psr\Http\Message\UriInterface;

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

    public function addJs(string $location, ?array $attributes = null, ?string $condition = null): void
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
            'attributes' => $attributes ?? [],
        ];
    }

    public function addCss(string $location, ?array $attributes = null, ?string $condition = null): void
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
            'attributes' => $attributes ?? [],
        ];
    }

    /**
     * @return string
     */
    public function renderJs(): string
    {
        $jsCode = '';

        foreach ($this->jsData as $jsData) {
            $jsCode .= $this->getScriptTag($jsData)."\n";
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
        foreach ($this->cssData as $cssData) {
            $cssCode .= $this->getCssLink($cssData)."\n";
        }

        return $cssCode;
    }

    public function getBaseUrl(): UriInterface
    {
        $uri = $this->config->getBaseUri();

        $path = $uri->getPath().'/static/';

        return $uri->withPath($path);
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
        return $this->getBaseUrl()->getPath();
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

    /**
     * Gets html code of the css loading
     *
     * @param array $data
     *
     * @return string
     */
    private function getCssLink(array $data): string
    {
        $location = $data['location'];
        $attributes = $data['attributes'];
        $condition = $data['condition'];

        $code = HTML::style($location, $attributes + ['media' => 'all']);

        if ($condition) {
            $code = $this->wrapInCondition($code, $condition);
        }

        return $code;
    }

    /**
     * Gets html code of the script loading
     *
     * @param array $data
     *
     * @return string
     */
    private function getScriptTag(array $data): string
    {
        $location = $data['location'];
        $attributes = $data['attributes'];
        $condition = $data['condition'];

        $code = HTML::script($location, $attributes);

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
